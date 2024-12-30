<?php 
session_start();

// Check if Level Coordinator is logged in
if (!isset($_SESSION['staff'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include("dbconnection.php");

// Fetch Level Coordinator's department and level from session
$department_id = $_SESSION['department_id']; 
$level = $_SESSION['level']; 

// Fetch students based on department and level
$stmt_students = $conn->prepare("SELECT name, registration_number, email, password, level FROM student WHERE department_id = ? AND level = ?");
$stmt_students->bind_param("ss", $department_id, $level);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

// Count total number of students
$total_students = $result_students->num_rows;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
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
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        td {
            color: #555;
        }
        p {
            text-align: center;
            font-size: 16px;
            color: #333;
            margin-top: 10px;
        }
        strong {
            color: #4CAF50;
        }
    </style>
</head>
<body>

<h2>View Students for Level <?php echo $level; ?></h2>

<!-- Students Table -->
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Registration Number</th>
            <th>Email</th>
            <th>Password</th>
            <th>Level</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result_students->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['registration_number']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['password']; ?></td>
            <td><?php echo $row['level']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Display the total number of students -->
<p><strong>Total Number of Students:</strong> <?php echo $total_students; ?></p>

</body>
</html>
