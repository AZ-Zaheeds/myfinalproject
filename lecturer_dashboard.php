<?php      
session_start();
include 'dbconnection.php';

// Check if the lecturer is logged in
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");  // Redirect to login if not logged in
    exit();
}

$lecturer_username = $_SESSION['staff'];

// Fetch the lecturer's department and name
$lecturer_sql = "SELECT s.name, s.department_id, d.department_name, s.course_id 
                 FROM staff_3 s 
                 JOIN department d ON s.department_id = d.department_id
                 WHERE s.username = ?";
$lecturer_stmt = mysqli_prepare($conn, $lecturer_sql);
mysqli_stmt_bind_param($lecturer_stmt, "s", $lecturer_username);
mysqli_stmt_execute($lecturer_stmt);
$lecturer_result = mysqli_stmt_get_result($lecturer_stmt);
$lecturer_data = mysqli_fetch_assoc($lecturer_result);

$lecturer_name = $lecturer_data['name'];
$department_name = $lecturer_data['department_name'];
$department_id = $lecturer_data['department_id'];
$assigned_courses = explode(',', $lecturer_data['course_id']); // Assuming multiple courses are comma-separated

// Fetch the details of assigned courses
$courses = [];
if (!empty($assigned_courses)) {
    $course_ids = "'" . implode("','", $assigned_courses) . "'"; // Format for SQL query
    $course_sql = "SELECT c.course_id, c.course_name FROM course c
                   WHERE c.course_id IN ($course_ids) AND c.department_id = '$department_id'";
    $course_result = mysqli_query($conn, $course_sql);

    while ($row = mysqli_fetch_assoc($course_result)) {
        $courses[] = $row;
    }
}

// Fetch students enrolled in the courses assigned to the lecturer
$students = [];
foreach ($courses as $course) {
    $course_id = $course['course_id'];
    $student_sql = "SELECT st.name, st.registration_number, sc.semester, sc.status, sc.level 
                    FROM student st
                    JOIN student_courses sc ON st.registration_number = sc.registration_number
                    WHERE sc.course_id = ? AND sc.status = 'Verified'"; // Only active students (Verified)
    $student_stmt = mysqli_prepare($conn, $student_sql);
    mysqli_stmt_bind_param($student_stmt, "s", $course_id);
    mysqli_stmt_execute($student_stmt);
    $student_result = mysqli_stmt_get_result($student_stmt);
    
    if (mysqli_num_rows($student_result) > 0) {
        $students[$course_id] = [];
        while ($student_row = mysqli_fetch_assoc($student_result)) {
            $students[$course_id][] = $student_row;
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <!-- Include your CSS links here -->
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="images/x-icon" href="images/ausu.ico">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/lecturer_dashboard.css">
    <style>
        .print-button {
            margin: 20px 0;
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .print-button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function printStudents(sectionId) {
            const printContent = document.getElementById(sectionId).innerHTML;
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
        }
    </script>
</head>
<body>

<!-- Navbar -->
<nav>
    <ul>
        <li><a href="lecturer_dashboard.php">Dashboard</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h1>Welcome, <?php echo $lecturer_name; ?>!</h1>
    <p>Department: <?php echo $department_name; ?></p>

    <!-- Display assigned courses -->
    <h2>Assigned Courses</h2>
    <?php if (empty($courses)): ?>
        <p>No course assigned yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo $course['course_id']; ?></td>
                        <td><?php echo $course['course_name']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    
    <?php if (empty($students)): ?>
        <p>No students registered for your courses.</p>
    <?php else: ?>
        
        <div id="students-section">
            <?php foreach ($students as $course_id => $student_list): ?>
                <h3>Course: <?php echo $courses[array_search($course_id, array_column($courses, 'course_id'))]['course_name']; ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Registration Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($student_list as $student): ?>
                            <tr>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['registration_number']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Print Button -->
    <button class="print-button" onclick="window.print()">Print students</button>
</div>

</body>
</html>
