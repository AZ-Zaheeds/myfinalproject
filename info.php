<?php 
// Database connection
require_once 'dbconnection.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['department_id']) || !isset($_SESSION['department_name'])) {
    header('Location: index.php');
    exit();
}

$department_id = $_SESSION['department_id'];
$department_name = $_SESSION['department_name'];

// Fetch courses for the department
$stmt = $conn->prepare("SELECT course_id, course_name FROM course WHERE department_id = ? ORDER BY course_name ASC");
if (!$stmt) {
    die("Error preparing SQL query for courses: " . $conn->error);
}
$stmt->bind_param("s", $department_id);
$stmt->execute();
courses = $stmt->get_result();

// Handle form submission for assigning courses
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_course'])) {
    $lecturer_id = $_POST['lecturer_id'];
    $course_id = $_POST['course_id'];

    $check_stmt = $conn->prepare("SELECT * FROM staff_3 WHERE lecturer_id = ? AND course_id = ?");
    if (!$check_stmt) {
        die("Error preparing SQL query for checking course assignment: " . $conn->error);
    }
    $check_stmt->bind_param("ss", $lecturer_id, $course_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "This course is already assigned to the selected lecturer.";
    } else {
        $assign_stmt = $conn->prepare("UPDATE staff_3 SET course_id = ? WHERE lecturer_id = ?");
        if (!$assign_stmt) {
            die("Error preparing SQL query for assigning course: " . $conn->error);
        }
        $assign_stmt->bind_param("ss", $course_id, $lecturer_id);
        if ($assign_stmt->execute()) {
            $success = "Course assigned successfully.";
        } else {
            $error = "Failed to assign course. Please try again.";
        }
    }
}

// Unassign course action
if (isset($_GET['unassign_course_id']) && isset($_GET['lecturer_id'])) {
    $lecturer_id = $_GET['lecturer_id'];

    $unassign_stmt = $conn->prepare("UPDATE staff_3 SET course_id = NULL WHERE lecturer_id = ?");
    if (!$unassign_stmt) {
        die("Error preparing SQL query for unassigning course: " . $conn->error);
    }
    $unassign_stmt->bind_param("s", $lecturer_id);
    if ($unassign_stmt->execute()) {
        $success = "Course unassigned successfully.";
    } else {
        $error = "Failed to unassign course. Please try again.";
    }
}

// Delete lecturer action
if (isset($_GET['delete_lecturer_id'])) {
    $lecturer_id = $_GET['delete_lecturer_id'];

    $delete_stmt = $conn->prepare("DELETE FROM staff_3 WHERE lecturer_id = ?");
    if (!$delete_stmt) {
        die("Error preparing SQL query for deleting lecturer: " . $conn->error);
    }
    $delete_stmt->bind_param("s", $lecturer_id);
    if ($delete_stmt->execute()) {
        $success = "Lecturer deleted successfully.";
    } else {
        $error = "Failed to delete lecturer. Please try again.";
    }
}

// Fetch lecturers for the department
$lecturers_stmt = $conn->prepare("SELECT lecturer_id, name FROM staff_3 WHERE department_id = ?");
if (!$lecturers_stmt) {
    die("Error preparing SQL query for fetching lecturers: " . $conn->error);
}
$lecturers_stmt->bind_param("s", $department_id);
$lecturers_stmt->execute();
$lecturers = $lecturers_stmt->get_result();

// Fetch lecturers with their assigned courses
$assigned_courses_stmt = $conn->prepare(
    "SELECT staff_3.name AS lecturer_name, course.course_name, staff_3.lecturer_id, staff_3.course_id 
     FROM staff_3 
     LEFT JOIN course ON staff_3.course_id = course.course_id 
     WHERE staff_3.department_id = ?"
);
if (!$assigned_courses_stmt) {
    die("Error preparing SQL query for fetching assigned courses: " . $conn->error);
}
$assigned_courses_stmt->bind_param("s", $department_id);
$assigned_courses_stmt->execute();
$assigned_courses = $assigned_courses_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Your styling here */
    </style>
</head>
<body>
    <div class="container">
        <h1>Lecturer Management</h1>

        <?php if (isset($success)): ?>
            <p class="success"> <?= $success ?> </p>
        <?php elseif (isset($error)): ?>
            <p class="error"> <?= $error ?> </p>
        <?php endif; ?>

        <!-- Add Lecturer Form -->
        <form action="" method="post">
            <!-- Fields for lecturer details -->
        </form>

        <!-- Assign Course Form -->
        <form action="" method="post">
            <!-- Fields for assigning course -->
        </form>

        <!-- View Assigned Courses -->
        <h2>Lecturers and Assigned Courses</h2>
        <table>
            <thead>
                <tr>
                    <th>Lecturer Name</th>
                    <th>Assigned Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $assigned_courses->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars(isset($row['lecturer_name']) ? $row['lecturer_name'] : '') ?></td>
                        <td><?= htmlspecialchars(isset($row['course_name']) ? $row['course_name'] : 'Unassigned') ?></td>
                        <td>
                            <a href="?unassign_course_id=<?= $row['course_id'] ?>&lecturer_id=<?= $row['lecturer_id'] ?>" class="action-btn">Unassign</a>
                            <a href="?delete_lecturer_id=<?= $row['lecturer_id'] ?>" class="action-btn" style="background-color: blue;">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
