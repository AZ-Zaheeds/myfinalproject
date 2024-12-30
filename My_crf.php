<?php  
// Include database connection
include('dbconnection.php');
session_start();

// Check if the student is logged in
if (!isset($_SESSION['student']) || !isset($_SESSION['department_id']) || !isset($_SESSION['level'])) {
    echo "Access Denied.";
    exit;
}

// Verify database connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Get session details
$registration_number = $_SESSION['student']; 
$department_id = $_SESSION['department_id'];
$level = $_SESSION['level'];

// Fetch student details
$query_student = "SELECT name, registration_number, level FROM student WHERE registration_number = '$registration_number'";
$result_student = mysqli_query($conn, $query_student);

if (!$result_student) {
    die("Query Failed: " . mysqli_error($conn));
}

$student = mysqli_fetch_assoc($result_student);
if (!$student) {
    echo "<p>Student not found. Please contact the admin.</p>";
    exit;
}

// Fetch registration status
$query_status = "SELECT status FROM student_courses 
                 WHERE registration_number = '$registration_number' AND level = '$level' LIMIT 1";
$result_status = mysqli_query($conn, $query_status);
$status_row = mysqli_fetch_assoc($result_status);

// Determine status and display appropriate message
$status = isset($status_row['status']) ? $status_row['status'] : 'No Status';


if ($status == 'Verified') {
    $status_message = "<p style='color: green;'>Your course registration has been verified. You can print your CRF.</p>";
    $allow_print = true;
} elseif ($status == 'Pending') {
    $status_message = "<p style='color: orange;'>Your course registration is pending review. Please wait for verification.</p>";
    $allow_print = false;
} elseif ($status == 'Not Verified') {
    $status_message = "<p style='color: red;'>Your course registration has been rejected. Please contact the admin.</p>";
    $allow_print = false;
} else {
    $status_message = "<p style='color: gray;'>You have not registered for courses yet.</p>";
    $allow_print = false;
}

// Fetch registered courses
$query_courses = "SELECT c.course_id, c.course_name, c.unit, sc.semester 
                  FROM student_courses sc 
                  JOIN course c ON sc.course_id = c.course_id 
                  WHERE sc.registration_number = '$registration_number' 
                  AND sc.level = '$level'";

$result_courses = mysqli_query($conn, $query_courses);

// Separate courses by semester
$courses_first = [];
$courses_second = [];

while ($course = mysqli_fetch_assoc($result_courses)) {
    if ($course['semester'] == 'First Semester') {
        $courses_first[] = $course;
    } else {
        $courses_second[] = $course;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Course Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2, h3, h4 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            display: block;
            width: 100px;
            margin: 20px auto;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        button:hover {
            background-color: #45a049;
        }
        .status-message {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>

<div class="container">
    <h2>My Course Registration Form</h2>

    <!-- Student Details -->
    <h3>Student Details</h3>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
    <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($student['registration_number']); ?></p>
    <p><strong>Level:</strong> <?php echo htmlspecialchars($student['level']); ?></p>
    <p><strong>Department:</strong> <?php echo htmlspecialchars($department_id); ?></p>

    <!-- Registration Status -->
    <div class="status-message">
        <?php echo $status_message; ?>
    </div>

    <?php if ($allow_print): ?>
        <!-- First Semester Courses -->
        <h4>First Semester Courses</h4>
        <?php if (count($courses_first) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $serial_number = 1; 
                    foreach ($courses_first as $course): ?>
                        <tr>
                            <td><?php echo $serial_number++; ?></td>
                            <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['unit']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No courses found for the First Semester.</p>
        <?php endif; ?>

        <!-- Second Semester Courses -->
        <h4>Second Semester Courses</h4>
        <?php if (count($courses_second) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $serial_number = 1; 
                    foreach ($courses_second as $course): ?>
                        <tr>
                            <td><?php echo $serial_number++; ?></td>
                            <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['unit']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No courses found for the Second Semester.</p>
        <?php endif; ?>

        <!-- Print Button -->
        <button onclick="printPage()">Print</button>
    <?php endif; ?>
</div>

</body>
</html>
