<?php   
// Include database connection
include('dbconnection.php');

// Start session
session_start();

// Check if the level coordinator is logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['department_id']) || !isset($_SESSION['level'])) {
    echo "Access Denied.";
    exit;
}

// Get the level coordinator's department and level from the session
$department_id = $_SESSION['department_id'];
$level = $_SESSION['level'];

// Fetch student registration number from URL
$registration_number = isset($_GET['registration_number']) ? $_GET['registration_number'] : '';

// Handle form submission for feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['status']) && $registration_number != '') {
        $status = $_POST['status']; // Get feedback (Verified, Not Verified, or Pending)
        $query_status = "UPDATE student_courses SET status = '$status' WHERE registration_number = '$registration_number'";
        
        if (mysqli_query($conn, $query_status)) {
            if ($status === 'Not Verified') {
                // If Not Verified, delete the courses
                $delete_courses = "DELETE FROM student_courses WHERE registration_number = '$registration_number'";
                mysqli_query($conn, $delete_courses);
                echo "<p>Courses have been removed. Student can re-register now.</p>";
            } else {
                echo "<p>Status successfully updated to Verified.</p>";
            }
        } else {
            echo "<p>Error submitting status: " . mysqli_error($conn) . "</p>";
        }
    }
}

// If a student is selected, fetch their details and courses
if ($registration_number != '') {
    // Fetch student details
    $query_student = "SELECT * FROM student WHERE registration_number = '$registration_number' AND department_id = '$department_id' AND level = '$level'";
    $result_student = mysqli_query($conn, $query_student);
    $student = mysqli_fetch_assoc($result_student);

    if ($student) {
        // Fetch First Semester Courses
        $query_courses_first = "SELECT c.course_name, c.unit, c.semester 
                                FROM student_courses sc 
                                JOIN course c ON sc.course_id = c.course_id 
                                WHERE sc.registration_number = '$registration_number' AND c.semester = 'First Semester'";
        $result_courses_first = mysqli_query($conn, $query_courses_first);

        // Fetch Second Semester Courses
        $query_courses_second = "SELECT c.course_name, c.unit, c.semester 
                                 FROM student_courses sc 
                                 JOIN course c ON sc.course_id = c.course_id 
                                 WHERE sc.registration_number = '$registration_number' AND c.semester = 'Second Semester'";
        $result_courses_second = mysqli_query($conn, $query_courses_second);
    } else {
        echo "<p>Student not found or does not match your department and level.</p>";
        exit;
    }
} else {
    // Fetch all students from the same department and level
    $query = "SELECT registration_number, name FROM student WHERE department_id = '$department_id' AND level = '$level'";
    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Course Registration Form</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
   

    <?php if ($registration_number == ''): ?>
        <!-- Display list of students -->
        <h3>Students List</h3>
        <ul>
            <?php while ($student = mysqli_fetch_assoc($result)): ?>
                <li>
                    <a href="Students_CRF.php?registration_number=<?php echo $student['registration_number']; ?>">
                        <?php echo htmlspecialchars($student['registration_number'] . ' - ' . $student['name']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <!-- Display selected student's course registration -->
        <h2>Course Registration Form for <?php echo htmlspecialchars($student['name']); ?></h2>

        <h3>First Semester Courses</h3>
        <?php if (mysqli_num_rows($result_courses_first) > 0): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = mysqli_fetch_assoc($result_courses_first)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['unit']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No courses found for the First Semester.</p>
        <?php endif; ?>

        <h3>Second Semester Courses</h3>
        <?php if (mysqli_num_rows($result_courses_second) > 0): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = mysqli_fetch_assoc($result_courses_second)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['unit']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No courses found for the Second Semester.</p>
        <?php endif; ?>

        <!-- Feedback Form -->
        <h3>Feedback</h3>
        <form method="POST">
            <label>
                <input type="radio" name="status" value="Verified" required> Verified
            </label>
            <label>
                <input type="radio" name="status" value="Not Verified" required> Not Verified
            </label>
            <br><br>
            <button type="submit">Submit Feedback</button>
        </form>

        <br>
        <a href="Students_CRF.php">Back to Student List</a>
    <?php endif; ?>
</body>
</html>
