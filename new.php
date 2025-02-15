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
    $department_id = $_SESSION['department_id'];
    $level = mysqli_real_escape_string($conn, $_POST['level']);

    // Check if course_id already exists
    $check_course = mysqli_query($conn, "SELECT * FROM course WHERE course_id = '$course_id'");
    if(mysqli_num_rows($check_course) > 0){
        $message = "Error: Course ID already exists!";
    } else {
        $sql = "INSERT INTO course (course_id, course_name, department_id, level) VALUES ('$course_id', '$course_name', '$department_id', '$level')";
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

// Fetch Level Coordinators
$coordinators = mysqli_query($conn, "SELECT * FROM staff_2 WHERE department_id = '$department_id'");

// Handle Student Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_students'])) {
    if (isset($_FILES['student_csv']) && $_FILES['student_csv']['error'] == 0) {
        $file_tmp = $_FILES['student_csv']['tmp_name'];
        $file_name = $_FILES['student_csv']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if (strtolower($file_ext) == 'csv') {
            if (($handle = fopen($file_tmp, "r")) !== FALSE) {
                // Assuming the first row contains headers
                fgetcsv($handle, 1000, ",");

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $student_id = mysqli_real_escape_string($conn, $data[0]);
                    $student_name = mysqli_real_escape_string($conn, $data[1]);
                    $email = mysqli_real_escape_string($conn, $data[2]);
                    $course_id = mysqli_real_escape_string($conn, $data[3]);

                    // Check if course_id exists
                    $check_course = mysqli_query($conn, "SELECT * FROM course WHERE course_id = '$course_id' AND department_id = '$department_id'");
                    if(mysqli_num_rows($check_course) > 0){
                        // Insert into students table
                        $sql = "INSERT INTO students (student_id, name, email, course_id) VALUES ('$student_id', '$student_name', '$email', '$course_id')";
                        mysqli_query($conn, $sql);
                    }
                }
                fclose($handle);
                $message = "Students uploaded successfully!";
            } else {
                $message = "Error opening the file.";
            }
        } else {
            $message = "Invalid file type. Please upload a CSV file.";
        }
    } else {
        $message = "Error uploading the file.";
    }
}

// Fetch students (optional: you can implement viewing students)
$students = mysqli_query($conn, "SELECT s.*, c.course_name FROM students s JOIN course c ON s.course_id = c.course_id WHERE c.department_id = '$department_id'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="css/hod_dashboard.css"> <!-- External CSS -->
    
    <style>
        /* Reset some basic elements */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }

        header {
            background-color: #28a745; /* Changed from blue to green */
            color: white;
            padding: 20px 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        header h1 {
            margin-bottom: 10px;
            font-size: 1.8em;
        }

        header h3 {
            margin-bottom: 10px;
            font-weight: normal;
            font-size: 1.2em;
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        nav ul li {
            display: inline;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background-color: #218838; /* Darker green for buttons */
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
        }

        nav ul li a:hover {
            background-color: #1e7e34;
        }

        /* Alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            font-size: 1em;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        main {
            max-width: 1200px;
            margin: 0 auto;
        }

        section {
            background-color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        section h2 {
            margin-bottom: 15px;
            border-bottom: 2px solid #28a745; /* Green underline */
            padding-bottom: 5px;
            color: #28a745;
        }

        .section-links {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .section-links a {
            background-color: #17a2b8; /* Teal color for section links */
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
        }

        .section-links a:hover {
            background-color: #138496;
        }

        /* Form styling */
        form {
            display: none;
            flex-direction: column;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form textarea,
        form select {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        form textarea {
            resize: vertical;
            height: 100px;
        }

        form button {
            padding: 10px;
            background-color: #28a745; /* Green button */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #218838;
        }

        /* Table styling */
        table {
            display: none;
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 0.95em;
        }

        table th {
            background-color: #28a745; /* Green header */
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .delete-button {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .delete-button:hover {
            color: #c82333;
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: #28a745; /* Green footer */
            color: white;
            border-radius: 5px;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                align-items: center;
            }

            .section-links {
                flex-direction: column;
                align-items: stretch;
            }

            .section-links a {
                text-align: center;
            }
        }
    </style>
    <script>
        // Function to show specific sections
        function showSection(sectionId) {
            var sections = document.querySelectorAll('form, table');
            sections.forEach(function(section) {
                section.style.display = 'none'; // Hide all sections
            });

            var section = document.getElementById(sectionId);
            if(section){
                section.style.display = 'block'; // Show the selected section
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (HOD)</h1>
        <h3>Department: <?php echo htmlspecialchars($_SESSION['department_name']); ?></h3>
        <nav>
            <ul>
                <li><a href="logout.php">Logout</a></li>
                <li><a href="#manage_courses" onclick="showSection('courseForm'); showSection('courseTable');">Manage Courses</a></li>
                <li><a href="#manage_coordinators" onclick="showSection('coordinatorForm'); showSection('coordinatorTable');">Manage Level Coordinators</a></li>
                <li><a href="#upload_students" onclick="showSection('studentForm'); showSection('studentTable');">Upload Students</a></li>
                <li><a href="#add_news" onclick="showSection('newsForm'); showSection('newsTable');">Add News Feed</a></li>
            </ul>
        </nav>
    </header>

    <?php 
    if (!empty($message)) { 
        // Differentiate message types (success vs error)
        $class = strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success';
        echo "<div class='alert $class'>{$message}</div>"; 
    } 
    ?>

    <main>
        <!-- Manage Courses Section -->
        <section id="manage_courses">
            <h2>Manage Courses</h2>
            <div class="section-links">
                <a href="#" onclick="showSection('courseForm')">Add New Course</a>
                <a href="#" onclick="showSection('courseTable')">View Courses</a>
            </div>
            <!-- Form to add new courses -->
            <form id="courseForm" method="POST" action="">
                <input type="text" name="course_id" placeholder="Course ID" required>
                <input type="text" name="course_name" placeholder="Course Name" required>
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
                        <th>Level</th>
                        <th>Action</th> <!-- New Action Column -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['level']); ?></td>
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
                            <td><?php echo htmlspecialchars($coordinator['username']); ?></td>
                            <td><?php echo htmlspecialchars($coordinator['level']); ?></td>
                            <td>
                                <a href="?delete_coordinator=<?php echo urlencode($coordinator['id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this coordinator?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
                <input type="file" name="student_csv" accept=".csv" required>
                <button type="submit" name="upload_students">Upload Students</button>
            </form>

            <!-- Existing Students Table -->
            <table id="studentTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = mysqli_fetch_assoc($students)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['course_name']); ?></td>
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
