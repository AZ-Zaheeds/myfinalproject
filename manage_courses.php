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

// Ensure department_id is set in the session
if (!isset($_SESSION['department_id'])) {
    $message = "Error: Department ID is not set!";
    exit();
}

// Store department_id from session
$department_id = $_SESSION['department_id'];

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $course_unit = mysqli_real_escape_string($conn, $_POST['course_unit']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);

    // Check if department_id exists in the department table
    $check_department = mysqli_query($conn, "SELECT * FROM department WHERE department_id = '$department_id'");
    if (mysqli_num_rows($check_department) == 0) {
        $message = "Error: Department ID does not exist!";
    } else {
        // Check if course_id already exists
        $check_course = mysqli_query($conn, "SELECT * FROM course WHERE course_id = '$course_id'");
        if(mysqli_num_rows($check_course) > 0){
            $message = "Error: Course ID already exists!";
        } else {
            // Insert new course into the course table
            $sql = "INSERT INTO course (course_id, course_name, unit, department_id, level, semester) 
                    VALUES ('$course_id', '$course_name', '$course_unit', '$department_id', '$level', '$semester')";
            if (mysqli_query($conn, $sql)) {
                $message = "Course added successfully!";
            } else {
                $message = "Error adding course: " . mysqli_error($conn);
            }
        }
    }
}

// Handle course level update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_level'])) {
    $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
    $new_level = mysqli_real_escape_string($conn, $_POST['new_level']);

    // Update course level
    $sql = "UPDATE course SET level = '$new_level' WHERE course_id = '$course_id'";
    if (mysqli_query($conn, $sql)) {
        $message = "Course level updated successfully!";
    } else {
        $message = "Error updating course level: " . mysqli_error($conn);
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
$courses = mysqli_query($conn, "SELECT * FROM course WHERE department_id = '$department_id'");
?>  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="css/hod_dashboard.css"> <!-- External CSS -->
    <style>
        /* [Your existing CSS styles here] */
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

        window.onload = function() {
            // Uncomment one of the following lines to show a section by default
            // showSection('courseForm');
            // showSection('courseTable');
        }
    </script>
</head>
<body>
    <?php 
    if (!empty($message)) { 
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
                <input type="number" name="course_unit" placeholder="Course Unit" required>
                <select name="semester">
                    <option>Select semester</option>
                    <option>First semester</option>
                    <option>Second semester</option>
                </select>
                <select name="level" required>
                    <option value="">Select Level</option>
                    <option value="100">100 Level</option>
                    <option value="200">200 Level</option>
                    <option value="300">300 Level</option>
                    <option value="400">400 Level</option>
                    <option value="500">500 Level</option>
                </select>
                <button type="submit" name="add_course">Add Course</button>
            </form>

            <!-- Table to display courses -->
            <table id="courseTable">
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Course Unit</th>
                        <th>Semester</th>
                        <th>Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['unit']); ?></td>
                        <td><?php echo htmlspecialchars($course['semester']); ?></td>
                        <td><?php echo htmlspecialchars($course['level']); ?></td>
                        <td>
                            <a href="manage_courses.php?delete_course=<?php echo urlencode($course['course_id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>

                            <!-- Edit button to display the edit level form -->
                           <!-- <a href="#" onclick="document.getElementById('editForm_<?php echo $course['course_id']; ?>').style.display='block';">Edit Level</a>-->
                            
                            <!-- Edit form (hidden initially) -->
                            <form id="editForm_<?php echo $course['course_id']; ?>" style="display: none;" method="POST" action="">
                                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                <select name="new_level" required>
                                    <option value="">Select New Level</option>
                                    <option value="100" <?php if ($course['level'] == 100) echo 'selected'; ?>>100 Level</option>
                                    <option value="200" <?php if ($course['level'] == 200) echo 'selected'; ?>>200 Level</option>
                                    <option value="300" <?php if ($course['level'] == 300) echo 'selected'; ?>>300 Level</option>
                                    <option value="400" <?php if ($course['level'] == 400) echo 'selected'; ?>>400 Level</option>
                                    <option value="500" <?php if ($course['level'] == 500) echo 'selected'; ?>>500 Level</option>
                                </select>
                                <button type="submit" name="update_level">Update Level</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        
    </footer>
</body>
</html>
 