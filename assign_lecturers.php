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
$courses = $stmt->get_result();

// Handle form submission for assigning courses
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_course'])) {
    $lecturer_id = $_POST['lecturer_id'];
    $course_id = $_POST['course_id'];

    // Check if the lecturer is already assigned to this course
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
        // Assign course
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

// Handle form submission for adding a new lecturer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_lecturer'])) {
    $lecturer_name = $_POST['lecturer_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Insert new lecturer into the staff_3 table without password hashing
    $add_stmt = $conn->prepare("INSERT INTO staff_3 (name, username, password, department_id) VALUES (?, ?, ?, ?)");
    if (!$add_stmt) {
        die("Error preparing SQL query for adding lecturer: " . $conn->error);
    }
    $add_stmt->bind_param("ssss", $lecturer_name, $username, $password, $department_id);

    if ($add_stmt->execute()) {
        $success = "New lecturer added successfully.";
    } else {
        $error = "Failed to add lecturer. Please try again.";
    }
}

// Handle unassigning lecturer from course
if (isset($_GET['unassign_course_id']) && isset($_GET['lecturer_id'])) {
    $lecturer_id = $_GET['lecturer_id'];
    $course_id = $_GET['unassign_course_id'];

    // Unassign the course
    $unassign_stmt = $conn->prepare("UPDATE staff_3 SET course_id = NULL WHERE lecturer_id = ? AND course_id = ?");
    if (!$unassign_stmt) {
        die("Error preparing SQL query for unassigning course: " . $conn->error);
    }
    $unassign_stmt->bind_param("ss", $lecturer_id, $course_id);

    if ($unassign_stmt->execute()) {
        $success = "Course unassigned successfully.";
    } else {
        $error = "Failed to unassign course. Please try again.";
    }
}

// Fetch lecturers for the department
$lecturers_stmt = $conn->prepare("SELECT lecturer_id, name, username, password FROM staff_3 WHERE department_id = ?");
if (!$lecturers_stmt) {
    die("Error preparing SQL query for fetching lecturers: " . $conn->error);
}
$lecturers_stmt->bind_param("s", $department_id);
$lecturers_stmt->execute();
$lecturers = $lecturers_stmt->get_result();

// Fetch lecturers with their assigned courses
$assigned_courses_stmt = $conn->prepare(
    "SELECT staff_3.name AS lecturer_name, course.course_name, staff_3.lecturer_id, course.course_id, staff_3.username, staff_3.password
     FROM staff_3 
     JOIN course ON staff_3.course_id = course.course_id 
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            border-radius: 10px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        form {
            margin-bottom: 30px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        table, th, td {
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        td a {
            background-color: #FF6347;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        td a:hover {
            background-color: #FF4500;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error {
            color: #f44336;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lecturer Management</h1>

        <?php if (isset($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <!-- Add Lecturer Form -->
        <form action="" method="post">
            <h2>Add New Lecturer</h2>
            <label for="lecturer_name">Lecturer Name:</label>
            <input type="text" name="lecturer_name" id="lecturer_name" required>

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" name="add_lecturer">Add Lecturer</button>
        </form>

        <!-- Assign Course Form -->
        <form action="" method="post">
            <h2>Assign Course to Lecturer</h2>
            <label for="lecturer_id">Select Lecturer:</label>
            <select name="lecturer_id" id="lecturer_id" required>
                <option value="">-- Select Lecturer --</option>
                <?php while ($row = $lecturers->fetch_assoc()): ?>
                    <option value="<?= $row['lecturer_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="course_id">Select Course:</label>
            <select name="course_id" id="course_id" required>
                <option value="">-- Select Course --</option>
                <?php while ($row = $courses->fetch_assoc()): ?>
                    <option value="<?= $row['course_id'] ?>"><?= htmlspecialchars($row['course_name']) ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="assign_course">Assign Course</button>
        </form>

        <!-- View Assigned Courses -->
        <h2>Lecturers and Assigned Courses</h2>
        <table>
            <thead>
                <tr>
                    <th>Lecturer Name</th>
                    <th>Assigned Course</th>
                    <th>Course ID</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $assigned_courses->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['lecturer_name']) ?></td>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                        <td><?= htmlspecialchars($row['course_id']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['password']) ?></td>
                        <td>
                            <a href="?unassign_course_id=<?= $row['course_id'] ?>&lecturer_id=<?= $row['lecturer_id'] ?>">Unassign</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</body>
</html>
<?php
// Database connection
require_once 'dbconnection.php';

// Check if the user is logged in

if (!isset($_SESSION['department_id']) || !isset($_SESSION['department_name'])) {
    header('Location: index.php');
    exit();
}

$department_id = $_SESSION['department_id'];
$department_name = $_SESSION['department_name'];

// Handle lecturer deletion
if (isset($_GET['delete_lecturer_id'])) {
    $lecturer_id = $_GET['delete_lecturer_id'];

    // Prepare and execute deletion query
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

// Fetch lecturers from the staff_3 table
$lecturers_stmt = $conn->prepare("SELECT lecturer_id, name, username, password FROM staff_3 WHERE department_id = ?");
if (!$lecturers_stmt) {
    die("Error preparing SQL query for fetching lecturers: " . $conn->error);
}
$lecturers_stmt->bind_param("s", $department_id);
$lecturers_stmt->execute();
$lecturers = $lecturers_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            border-radius: 10px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        table, th, td {
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        td a {
            background-color: #FF6347;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        td a:hover {
            background-color: #FF4500;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error {
            color: #f44336;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lecturer Management</h1>

        <?php if (isset($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <!-- Lecturer Table -->
        <h2>All Lecturers</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $lecturers->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['password']) ?></td>
                        <td>
                            <a href="?delete_lecturer_id=<?= $row['lecturer_id'] ?>" onclick="return confirm('Are you sure you want to delete this lecturer?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
