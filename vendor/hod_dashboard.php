<?php   
session_start();

// Check if the user is logged in as HOD
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

include 'dbconnection.php';

// Initialize message variable
$message = "";

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $course_unit = mysqli_real_escape_string($conn, $_POST['course_unit']);
    $course_semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $department_id = $_SESSION['department_id'];
    $level = mysqli_real_escape_string($conn, $_POST['level']);

    // Check if course_id already exists
    $check_course = mysqli_query($conn, "SELECT * FROM course WHERE course_id = '$course_id'");
    if(mysqli_num_rows($check_course) > 0){
        $message = "Error: Course ID already exists!";
    } else {
        $sql = "INSERT INTO course (course_id, course_name,unit,semester,department_id, level) VALUES ('$course_id', '$course_name', '$course_unit','$course_semester','$department_id', '$level')";
        if (mysqli_query($conn, $sql)) {
            $message = "Course added successfully!";
        } else {
            $message = "Error adding course: " . mysqli_error($conn);
        }
    }
}

// Handle course deletion
if (isset($_GET['delete_course'])) {
    $course_id = mysqli_real_escape_string($conn, $_GET['delete_course']);
    $sql = "DELETE FROM course WHERE course_id = '$course_id'";
    if (mysqli_query($conn, $sql)) {
        $message = "Course deleted successfully!";
    } else {
        $message = "Error deleting course: " . mysqli_error($conn);
    }
}

// Fetch courses for the HOD's department
$department_id = $_SESSION['department_id'];
$courses = mysqli_query($conn, "SELECT * FROM course WHERE department_id = '$department_id'");

// Handle news feed addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $news_title = mysqli_real_escape_string($conn, $_POST['news_title']);
    $news_content = mysqli_real_escape_string($conn, $_POST['news_content']);

    $sql = "INSERT INTO news_feed (title, content) VALUES ('$news_title', '$news_content')";
    if (mysqli_query($conn, $sql)) {
        $message = "News added successfully!";
    } else {
        $message = "Error adding news: " . mysqli_error($conn);
    }
}

// Handle news feed deletion
if (isset($_GET['delete_news'])) {
    $news_id = mysqli_real_escape_string($conn, $_GET['delete_news']);
    
    // Optional: Check if the news feed exists before attempting deletion
    $check_news = mysqli_query($conn, "SELECT * FROM news_feed WHERE id = '$news_id'");
    if(mysqli_num_rows($check_news) > 0){
        $sql = "DELETE FROM news_feed WHERE id = '$news_id'";
        if (mysqli_query($conn, $sql)) {
            $message = "News deleted successfully!";
        } else {
            $message = "Error deleting news: " . mysqli_error($conn);
        }
    } else {
        $message = "Error: News feed not found!";
    }
}

// Fetch existing news feeds
$news_feeds = mysqli_query($conn, "SELECT * FROM news_feed ORDER BY created_at DESC");

// Handle Level Coordinator Management
// Add Level Coordinator
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_coordinator'])) {
    $coordinator_name = mysqli_real_escape_string($conn, $_POST['coordinator_name']);
    $coordinator_username = mysqli_real_escape_string($conn, $_POST['coordinator_username']);
    $coordinator_password = mysqli_real_escape_string($conn, $_POST['coordinator_password']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);

    // Check if username already exists
    $check_username = mysqli_query($conn, "SELECT * FROM staff_2 WHERE username = '$coordinator_username'");
    if(mysqli_num_rows($check_username) > 0){
        $message = "Error: Username already exists!";
    } else {
        // **Important**: Hash the password before storing it
        $hashed_password = password_hash($coordinator_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO staff_2 (name, username, password, department_id, level) VALUES ('$coordinator_name', '$coordinator_username', '$hashed_password', '$department_id', '$level')";
        if (mysqli_query($conn, $sql)) {
            $message = "Level Coordinator added successfully!";
        } else {
            $message = "Error adding coordinator: " . mysqli_error($conn);
        }
    }
}

// Delete Level Coordinator
if (isset($_GET['delete_coordinator'])) {
    $coordinator_id = mysqli_real_escape_string($conn, $_GET['delete_coordinator']);
    $sql = "DELETE FROM staff_2 WHERE id = '$coordinator_id'";
    if (mysqli_query($conn, $sql)) {
        $message = "Level Coordinator deleted successfully!";
    } else {
        $message = "Error deleting coordinator: " . mysqli_error($conn);
    }
}

// Handle Level Coordinator Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_coordinator'])) {
    $coordinator_id = mysqli_real_escape_string($conn, $_POST['coordinator_id']);
    $new_level = mysqli_real_escape_string($conn, $_POST['new_level']);

    $sql = "UPDATE staff_2 SET level = '$new_level' WHERE id = '$coordinator_id' AND department_id = '$department_id'";
    if (mysqli_query($conn, $sql)) {
        $message = "Level Coordinator updated successfully!";
    } else {
        $message = "Error updating coordinator: " . mysqli_error($conn);
    }
}

// Fetch Level Coordinators
$coordinators = mysqli_query($conn, "SELECT * FROM staff_2 WHERE department_id = '$department_id'");

// Fetch student data
$students = mysqli_query($conn, "SELECT s.*, c.course_name FROM students s JOIN course c ON s.course_id = c.course_id WHERE c.department_id = '$department_id'");

// Handle Student Upload
// Handle Student Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_students'])) {
    if (isset($_FILES['student_excel']) && $_FILES['student_excel']['error'] == 0) {
        $file_tmp = $_FILES['student_excel']['tmp_name'];
        $file_name = $_FILES['student_excel']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if (strtolower($file_ext) == 'xls' || strtolower($file_ext) == 'xlsx') {
            // Load PhpSpreadsheet
            require 'vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed

           use PhpOffice\PhpSpreadsheet\IOFactory;

            if (($spreadsheet = IOFactory::load($file_tmp)) !== FALSE) {
                $sheet = $spreadsheet->getActiveSheet();
                
                // Assuming the first row contains headers, start reading from the second row
                foreach ($sheet->getRowIterator(2) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }

                    $name = mysqli_real_escape_string($conn, $rowData[0]);
                    $registration_number = mysqli_real_escape_string($conn, $rowData[1]);
                    $password = mysqli_real_escape_string($conn, $rowData[2]);
                    $email = mysqli_real_escape_string($conn, $rowData[3]);
                    $department_id = mysqli_real_escape_string($conn, $rowData[4]);
                    $level = mysqli_real_escape_string($conn, $rowData[5]);
                    // Check if course_id exists
                    $check_course = mysqli_query($conn, "SELECT * FROM course WHERE course_id = '$course_id' AND department_id = '$department_id'");
                    if (mysqli_num_rows($check_course) > 0) {
                        // Insert into students table
                        $sql = "INSERT INTO students (name,registration_number,password, email,department_id,level) VALUES ('$name', '$registration_number', '$password', '$email','$department_id', '$level')";
                        mysqli_query($conn, $sql);
                    }
                }
                $message = "Students uploaded successfully!";
            } else {
                $message = "Error opening the Excel file.";
            }
        } else {
            $message = "Invalid file type. Please upload an Excel file (.xls or .xlsx).";
        }
    } else {
        $message = "Error uploading the file.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="css/hod_dashboard.css"> <!-- External CSS -->
    <script src="javascrpt/show_section_form.js"></script>       
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (HOD)</h1>
        <h3>Department: <?php echo htmlspecialchars($_SESSION['department_name']); ?></h3>
        <nav>
            <ul>
                <li><a href="#manage_courses" onclick="showSection('courseForm'); showSection('courseTable');">Manage Courses</a></li>
                <li><a href="#manage_coordinators" onclick="showSection('coordinatorForm'); showSection('coordinatorTable');">Manage Level Coordinators</a></li>
                <li><a href="#upload_students" onclick="showSection('studentForm'); showSection('studentTable');">Upload Students</a></li>
                <li><a href="#add_news" onclick="showSection('newsForm'); showSection('newsTable');">Add News Feed</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <?php if (!empty($message)) { 
        // Differentiate message types (success vs error)
        $class = strpos($message, 'Error') !== false ? 'alert' : 'alert alert-success';
        echo "<div class='$class'>{$message}</div>"; 
    } ?>

    <main>
        <!-- Manage Courses Section -->
        <section id="manage_courses">
            <h2>Manage Courses</h2>
            <div class="section-links">
                <a href="#" onclick="showSection('courseForm')">Add New Course</a>
                <a href="#" onclick="showSection('courseTable')">View Courses</a>
            </div>
            <!-- Form to add new courses -->
            <form id="courseForm" method="POST" action="hod_dashboard.php">
                <input type="text" name="course_id" placeholder="Course ID" required>
                <input type="text" name="course_name" placeholder="Course Name" required>
                <input type="number" name="unit" placeholder="Course unit" required>
                <input type="text" name="semester" placeholder="semester" required>              
                <select name="level" required>
                    <option value="">Select Level</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                <button type="submit" name="add_course">Add Course</button>
            </form>

            <!-- Existing Courses Table -->
            <table id="courseTable">
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>unit</th>
                        <th>Level</th>
                        <th>Semester</th>
                        <th>Action</th> <!-- New Action Column -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['unit']); ?></td>
                            <td><?php echo htmlspecialchars($course['level']); ?></td>
                            <td><?php echo htmlspecialchars($course['semester']); ?></td>
                            <td>
                                <a href="?delete_course=<?php echo urlencode($course['course_id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Manage Level Coordinators Section -->
        <section id="manage_coordinators">
            <h2>Manage Level Coordinators</h2>
            <div class="section-links">
                <a href="#" onclick="showSection('coordinatorForm')">Add Coordinator</a>
                <a href="#" onclick="showSection('coordinatorTable')">View Coordinators</a>
            </div>
            <!-- Form to add a level coordinator -->
            <form id="coordinatorForm" method="POST" action="">
                <input type="text" name="coordinator_name" placeholder="Coordinator Name" required>
                <input type="text" name="coordinator_username" placeholder="Coordinator Username" required>
                <input type="password" name="coordinator_password" placeholder="Coordinator Password" required>
                <select name="level" required>
                    <option value="">Select Level</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                <button type="submit" name="add_coordinator">Add Coordinator</button>
            </form>

            <!-- Existing Level Coordinators Table -->
            <table id="coordinatorTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>password</th>
                        <th>Username</th>
                        <th>Level</th>
                        <th>Action</th> <!-- New Action Column -->
                    </tr>
                </thead>
                <tbody>
                <?php while ($coordinator = mysqli_fetch_assoc($coordinators)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($coordinator['id']); ?></td>
                        <td><?php echo htmlspecialchars($coordinator['name']); ?></td>
                        <td><?php echo htmlspecialchars($coordinator['password']); ?></td>
                        <td><?php echo htmlspecialchars($coordinator['username']); ?></td>
                        <td><?php echo htmlspecialchars($coordinator['level']); ?></td>
                        <td>
                            <a href="javascript:void(0);" class="edit-button" onclick="showEditForm('<?php echo $coordinator['id']; ?>', '<?php echo $coordinator['level']; ?>')">Edit</a>
                            <a href="?delete_coordinator=<?php echo urlencode($coordinator['id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this coordinator?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Edit Coordinator Form -->
            <form id="editForm" class="edit-form" method="POST" action="">
                <h3>Edit Coordinator Level</h3>
                <input type="hidden" name="coordinator_id" id="edit_coordinator_id" value="">
                <label for="new_level">New Level:</label>
                <select name="new_level" id="new_level" required>
                    <option value="">Select Level</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                <div style="margin-top: 10px;">
                    <button type="submit" name="update_coordinator">Update Level</button>
                    <button type="button" class="cancel-button" onclick="cancelEdit()">Cancel</button>
                </div>
            </form>
        </section>

        <!-- Upload Students Section -->
        <section id="upload_students">
            <h2>Upload Students</h2>
            <div class="section-links">
                <a href="#" onclick="showSection('studentForm')">Upload Students</a>
                <a href="#" onclick="showSection('studentTable')">View Students</a>
            </div>
            <!-- Form to upload students via CSV -->
            <form id="studentForm" method="POST" action="" enctype="multipart/form-data">
                <input type="file" name="student_excel" accept=".xls,.xlsx" required>
                <button type="submit" name="upload_students">Upload Students</button>
            </form>

            <!-- Existing Students Table -->
            <table id="studentTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Registration_number</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>Department_id</th>
                        <th>level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = mysqli_fetch_assoc($students)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['password']); ?></td>
                            <td><?php echo htmlspecialchars($student['department_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['level']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Add News Feed Section -->
        <section id="add_news">
            <h2>Add News Feed</h2>
            <div class="section-links">
                <a href="#" onclick="showSection('newsForm')">Add News</a>
                <a href="#" onclick="showSection('newsTable')">View News</a>
            </div>
            <!-- Form to add news feed -->
            <form id="newsForm" method="POST" action="">
                <input type="text" name="news_title" placeholder="News Title" required>
                <textarea name="news_content" placeholder="News Content" required></textarea>
                <button type="submit" name="add_news">Add News</button>
            </form>

            <!-- Existing News Feeds Table -->
            <table id="newsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Posted At</th>
                        <th>Action</th> <!-- New Action Column -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($news = mysqli_fetch_assoc($news_feeds)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($news['title']); ?></td>
                            <td><?php echo htmlspecialchars($news['content']); ?></td>
                            <td><?php echo htmlspecialchars($news['created_at']); ?></td>
                            <td>
                                <a href="?delete_news=<?php echo urlencode($news['id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this news feed?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Al-Istiqama University, Sumaila. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
mysqli_close($conn);
?>
