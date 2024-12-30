<?php
session_start();

// Check if Level Coordinator is logged in
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include("dbconnection.php");

// Fetch Level Coordinator details from session
$department_id = $_SESSION['department_id']; 
$department_name=$_SESSION['department_name'];
$level = $_SESSION['level']; 

// Default values for max and min units
$max_units_first = 0;
$min_units_first = 0;
$max_units_second = 0;
$min_units_second = 0;

// Handle form submission to update unit limits
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_limits'])) {
    $max_units_first = $_POST['max_units_first'];
    $min_units_first = $_POST['min_units_first'];
    $max_units_second = $_POST['max_units_second'];
    $min_units_second = $_POST['min_units_second'];

    // Update the unit limits for the department and level
    // First Semester Update
    $stmt_update_first = $conn->prepare("INSERT INTO registration_settings (department_id, level, semester, max_units, min_units) 
                                        VALUES (?, ?, 'First Semester', ?, ?) 
                                        ON DUPLICATE KEY UPDATE max_units = ?, min_units = ?");
    $stmt_update_first->bind_param("ssiiii", $department_id, $level, $max_units_first, $min_units_first, $max_units_first, $min_units_first);
    $stmt_update_first->execute();

    // Second Semester Update
    $stmt_update_second = $conn->prepare("INSERT INTO registration_settings (department_id, level, semester, max_units, min_units) 
                                        VALUES (?, ?, 'Second Semester', ?, ?) 
                                        ON DUPLICATE KEY UPDATE max_units = ?, min_units = ?");
    $stmt_update_second->bind_param("ssiiii", $department_id, $level, $max_units_second, $min_units_second, $max_units_second, $min_units_second);
    $stmt_update_second->execute();
}

// Fetch current maximum and minimum unit limits for the department and level
$stmt_limits = $conn->prepare("SELECT semester, max_units, min_units FROM registration_settings WHERE department_id = ? AND level = ?");
$stmt_limits->bind_param("ss", $department_id, $level);
$stmt_limits->execute();
$result_limits = $stmt_limits->get_result();

// Initialize default values for the limits
while ($row = $result_limits->fetch_assoc()) {
    if ($row['semester'] == 'First Semester') {
        $max_units_first = $row['max_units'];
        $min_units_first = $row['min_units'];
    }
    if ($row['semester'] == 'Second Semester') {
        $max_units_second = $row['max_units'];
        $min_units_second = $row['min_units'];
    }
}

// Fetch First Semester courses and total units
$stmt_first = $conn->prepare("SELECT course_id, course_name, unit, visible FROM course WHERE department_id = ? AND level = ? AND semester = 'First Semester'");
$stmt_first->bind_param("ss", $department_id, $level);
$stmt_first->execute();
$result_first = $stmt_first->get_result();

// Check if courses are returned for the first semester
if ($result_first->num_rows > 0) {
    $courses_first = $result_first->fetch_all(MYSQLI_ASSOC);  // Fetch all courses
    // Calculate total units for First Semester
    $total_units_first = 0;
    foreach ($courses_first as $course) {
        $total_units_first += $course['unit'];
    }
} else {
    $courses_first = [];  // No courses found
    $total_units_first = 0;
}

// Fetch Second Semester courses and total units
$stmt_second = $conn->prepare("SELECT course_id, course_name, unit, visible FROM course WHERE department_id = ? AND level = ? AND semester = 'Second Semester'");
$stmt_second->bind_param("ss", $department_id, $level);
$stmt_second->execute();
$result_second = $stmt_second->get_result();

// Check if courses are returned for the second semester
if ($result_second->num_rows > 0) {
    $courses_second = $result_second->fetch_all(MYSQLI_ASSOC);  // Fetch all courses
    // Calculate total units for Second Semester
    $total_units_second = 0;
    foreach ($courses_second as $course) {
        $total_units_second += $course['unit'];
    }
} else {
    $courses_second = [];  // No courses found
    $total_units_second = 0;
}

// Handle course visibility toggle
if (isset($_POST['toggle_visibility'])) {
    $course_id = $_POST['course_id'];
    $current_visibility = $_POST['current_visibility'];
    $new_visibility = ($current_visibility == 1) ? 0 : 1;  // Toggle visibility

    // Update course visibility
    $stmt_toggle = $conn->prepare("UPDATE course SET visible = ? WHERE course_id = ?");
    $stmt_toggle->bind_param("ii", $new_visibility, $course_id);
    $stmt_toggle->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level Coordinator Dashboard</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        h3 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .visible {
            color: green;
        }
        .hidden {
            color: red;
        }
        .units-info {
            font-weight: bold;
            color: #333;
            text-align: right;
            margin-top: 10px;
        }
        .units-info strong {
            color: #4CAF50;
        }
        .form-container {
            margin-bottom: 30px;
        }
        .form-container input {
            padding: 10px;
            font-size: 16px;
            margin: 5px;
            width: 200px;
        }
        .form-container button {
            padding: 10px 15px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- Form to specify or update unit limits -->
<div class="form-container">
    <h3>Set Unit Limits for First and Second Semesters</h3>
    <form action="manage_courses_levelcoord.php" method="POST">
        <label for="max_units_first">Max Units (First Semester):</label>
        <input type="number" id="max_units_first" name="max_units_first" value="<?php echo $max_units_first; ?>" required>
        <br>
        <label for="min_units_first">Min Units (First Semester):</label>
        <input type="number" id="min_units_first" name="min_units_first" value="<?php echo $min_units_first; ?>" required>
        <br>
        <label for="max_units_second">Max Units (Second Semester):</label>
        <input type="number" id="max_units_second" name="max_units_second" value="<?php echo $max_units_second; ?>" required>
        <br>
        <label for="min_units_second">Min Units (Second Semester):</label>
        <input type="number" id="min_units_second" name="min_units_second" value="<?php echo $min_units_second; ?>" required>
        <br>
        <button type="submit" name="update_limits">Update Limits</button>
    </form>
</div>

<div>
<div style="padding: 20px;">
        <p><strong>Department:</strong> <?php echo $department_name; ?></p>
        <p><strong>Level:</strong> <?php echo $level; ?></p>
    </div>
<!-- First Semester Courses -->
<h3>First Semester Courses</h3>
<?php if (count($courses_first) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Toggle Visibility</th>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Course Units</th>
                <th>Visibility</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses_first as $course): ?>
            <tr>
                <td>
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                        <input type="hidden" name="current_visibility" value="<?php echo $course['visible']; ?>">
                        <button type="submit" name="toggle_visibility"><?php echo $course['visible'] == 1 ? 'Hide' : 'Show'; ?></button>
                    </form>
                </td>
                <td><?php echo $course['course_id']; ?></td>
                <td><?php echo $course['course_name']; ?></td>
                <td><?php echo $course['unit']; ?></td>
                <td class="<?php echo $course['visible'] == 1 ? 'visible' : 'hidden'; ?>">
                    <?php echo $course['visible'] == 1 ? 'Visible' : 'Hidden'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No courses available for the First Semester.</p>
<?php endif; ?>
<!-- Display Max and Min Units Info -->
<div class="units-info">
    <p>First Semester: <strong>Max:</strong> <?php echo $max_units_first; ?> | <strong>Min:</strong> <?php echo $min_units_first; ?> | <strong>Total Units:</strong> <?php echo $total_units_first; ?></p>
</div>
    <!-- Second Semester Courses -->
<h3> Second Semester Courses</h3>
<?php if (count($courses_second) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Toggle Visibility</th>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Course Units</th>
                <th>Visibility</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses_second as $course): ?>
            <tr>
                <td>
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                        <input type="hidden" name="current_visibility" value="<?php echo $course['visible']; ?>">
                        <button type="submit" name="toggle_visibility"><?php echo $course['visible'] == 1 ? 'Hide' : 'Show'; ?></button>
                    </form>
                </td>
                <td><?php echo $course['course_id']; ?></td>
                <td><?php echo $course['course_name']; ?></td>
                <td><?php echo $course['unit']; ?></td>
                <td class="<?php echo $course['visible'] == 1 ? 'visible' : 'hidden'; ?>">
                    <?php echo $course['visible'] == 1 ? 'Visible' : 'Hidden'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No courses available for the Second Semester.</p>
<?php endif; ?>
<!-- Display Max and Min Units Info -->
<div class="units-info">
<p>Second Semester: <strong>Max:</strong> <?php echo $max_units_second; ?> | <strong>Min:</strong> <?php echo $min_units_second; ?> | <strong>Total Units:</strong> <?php echo $total_units_second; ?></p>
</div>

<!-- Print Button -->
<button class="print-button" onclick="window.print()">Print Courses</button>

</div>
</body>
</html>
