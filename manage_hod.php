<?php
// Include database connection
include 'dbconnection.php';

// Fetch departments for the dropdown
$departments = $conn->query("SELECT department_id, department_name FROM department");

// Insert new HOD if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = $_POST['name'];
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $department_id = $_POST['department'];

    // Check if the username already exists in the staff_1 table
    $check_sql = "SELECT * FROM staff_1 WHERE username='$new_username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>alert('HOD already exists with the same username.');</script>";
    } else {
        // Insert into the staff_1 table
        $sql = "INSERT INTO staff_1 (name, username, password, department_id) VALUES ('$new_name', '$new_username', '$new_password', '$department_id')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('New HOD added successfully.');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

// Delete HOD if delete button is pressed
if (isset($_GET['delete'])) {
    $username_to_delete = $_GET['delete'];

    // Delete from the staff_1 table
    $delete_sql = "DELETE FROM staff_1 WHERE username='$username_to_delete'";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('HOD deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Fetch existing HODs
$result = $conn->query("SELECT staff_1.name, staff_1.username, staff_1.password, department.department_name FROM staff_1 JOIN department ON staff_1.department_id = department.department_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage HODs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        .container {
            width: 60%;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        form {
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }
        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .delete-button {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage HODs</h2>

    <!-- Form to add new HOD -->
    <form method="POST" action="">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="username">Username:</label>
        <input type="email" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <label for="department">Assign Department:</label>
        <select id="department" name="department" required>
            <option value="">Select Department</option>
            <?php
            if ($departments->num_rows > 0) {
                while ($dept = $departments->fetch_assoc()) {
                    echo "<option value='" . $dept['department_id'] . "'>" . $dept['department_name'] . "</option>";
                }
            } else {
                echo "<option value=''>No departments found</option>";
            }
            ?>
        </select>
        <input type="submit" value="Add HOD">
    </form>

    <!-- Display existing HODs -->
    <table>
        <tr>
            <th>Name</th>
            <th>Username</th>
            <th>Password</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . htmlspecialchars($row["name"]) . "</td><td>" . htmlspecialchars($row["username"]) . "</td><td>" . htmlspecialchars($row["password"]) . "</td><td>" . htmlspecialchars($row["department_name"]) . "</td>
                <td><a class='delete-button' href='?delete=" . urlencode($row["username"]) . "' onclick='return confirm(\"Are you sure you want to delete this HOD?\");'>Delete</a></td></tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No HODs found</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>
