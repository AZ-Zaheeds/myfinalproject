<?php      
session_start();
include("dbconnection.php");

// Check if the student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: index.php");
    exit();
}

// Fetch student details
$registrationNumber = $_SESSION['student'];
$level = $_SESSION['level'];
$departmentId = $_SESSION['department_id']; // Department ID stored in session

// Variables
$message = "";
$courseQueue = [];

// Initialize or retrieve course queue from session
if (!isset($_SESSION['course_queue'])) {
    $_SESSION['course_queue'] = [];
}
$courseQueue = $_SESSION['course_queue'];

// Fetch min and max units for each semester
$unitsQuery = $conn->prepare("SELECT semester, min_units, max_units 
                              FROM registration_settings 
                              WHERE department_id = ? AND level = ?");
$unitsQuery->bind_param("si", $departmentId, $level);
$unitsQuery->execute();
$resultUnits = $unitsQuery->get_result();

$semesterUnits = [];
while ($row = $resultUnits->fetch_assoc()) {
    $semesterUnits[$row['semester']] = [
        'min' => $row['min_units'],
        'max' => $row['max_units']
    ];
}

// Handle Add to Queue
if (isset($_POST['add_to_queue'])) {
    $courseId = $_POST['course_id'];

    // Check if the course exists and belongs to the student's department
    $courseQuery = $conn->prepare("SELECT * FROM course WHERE course_id = ? AND department_id = ?");
    $courseQuery->bind_param("si", $courseId, $departmentId);
    $courseQuery->execute();
    $result = $courseQuery->get_result();

    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
        
        // Check the 'visible' column before adding to queue
        if ($course['visible'] == 0) {
            $message = "<div style='color: red;'>Can't add course to queue currently.</div>";
        } else {
            // Prevent duplicates
            if (!in_array($courseId, array_column($courseQueue, 'course_id'))) {
                $_SESSION['course_queue'][] = $course;
                $courseQueue = $_SESSION['course_queue'];
                $message = "<div style='color: green;'>Course added to queue.</div>";
            } else {
                $message = "<div style='color: red;'>Course already in queue.</div>";
            }
        }
    } else {
        $message = "<div style='color: red;'>Invalid or Department Mismatch Course ID.</div>";
    }
}

// Handle Course Registration
if (isset($_POST['register_courses'])) {
    $selectedCourses = isset($_POST['selected_courses']) ? $_POST['selected_courses'] : [];
    $totalUnitsPerSemester = ['First Semester' => 0, 'Second Semester' => 0];

    // Calculate units
    foreach ($courseQueue as $course) {
        if (in_array($course['course_id'], $selectedCourses)) {
            $totalUnitsPerSemester[$course['semester']] += $course['unit'];
        }
    }

    // Validate units
    $errors = [];
    foreach ($semesterUnits as $semester => $limits) {
        if ($totalUnitsPerSemester[$semester] < $limits['min'] || $totalUnitsPerSemester[$semester] > $limits['max']) {
            $errors[] = "For <strong>$semester</strong>, total units must be between {$limits['min']} and {$limits['max']}. Current: {$totalUnitsPerSemester[$semester]} units.";
        }
    }

    if (!empty($errors)) {
        $message = "<div style='color: red;'>" . implode("<br>", $errors) . "</div>";
    } else {
        foreach ($courseQueue as $course) {
            if (in_array($course['course_id'], $selectedCourses)) {
                $checkQuery = $conn->prepare("SELECT * FROM student_courses WHERE registration_number = ? AND course_id = ?");
                $checkQuery->bind_param("ss", $registrationNumber, $course['course_id']);
                $checkQuery->execute();
                $result = $checkQuery->get_result();

                if ($result->num_rows == 0) {
                    $insertQuery = $conn->prepare("INSERT INTO student_courses (registration_number, course_id, level, semester) VALUES (?, ?, ?, ?)");
                    $insertQuery->bind_param("ssis", $registrationNumber, $course['course_id'], $level, $course['semester']);
                    $insertQuery->execute();
                }
            }
        }
        $message = "<div style='color: green;'>Courses registered successfully!</div>";
        $_SESSION['course_queue'] = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Registration</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: center; }
        .btn { padding: 8px 12px; background: #28a745; color: #fff; border: none; cursor: pointer; }
        .message { text-align: center; margin-bottom: 15px; }
        .search-bar { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Course Registration</h1>
        <?php echo $message; ?>

        <form method="POST" class="search-bar">
            <label>Search Course by Course ID:</label>
            <input type="text" name="course_id" placeholder="Enter Course ID" required>
            <button type="submit" name="add_to_queue" class="btn">Add to Queue</button>
        </form>

        <h4>First Semester</h4>
        <form method="POST">
            <table>
                <tr>
                    <th>Select</th><th>Course ID</th><th>Course Name</th><th>Units</th>
                </tr>
                <?php
                foreach ($courseQueue as $course) {
                    if ($course['semester'] === 'First Semester') {
                        echo "<tr>
                            <td><input type='checkbox' name='selected_courses[]' value='{$course['course_id']}'></td>
                            <td>{$course['course_id']}</td>
                            <td>{$course['course_name']}</td>
                            <td>{$course['unit']}</td>
                        </tr>";
                    }
                }
                ?>
            </table>

            <h4>Second Semester</h4>
            <table>
                <tr>
                    <th>Select</th><th>Course ID</th><th>Course Name</th><th>Units</th>
                </tr>
                <?php
                foreach ($courseQueue as $course) {
                    if ($course['semester'] === 'Second Semester') {
                        echo "<tr>
                            <td><input type='checkbox' name='selected_courses[]' value='{$course['course_id']}'></td>
                            <td>{$course['course_id']}</td>
                            <td>{$course['course_name']}</td>
                            <td>{$course['unit']}</td>
                        </tr>";
                    }
                }
                ?>
            </table>
            <br>
            <button type="submit" name="register_courses" class="btn">Register Selected Courses</button>
        </form>
     
    </div>
</body>
</html>

<?php $conn->close(); ?>
