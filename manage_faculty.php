<?php
// Include database connection
include 'dbconnection.php';

// Initialize variables for error/success messages
$message = "";

// Handle Add Faculty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faculty'])) {
    $new_faculty_id = trim($_POST['faculty_id']);
    $new_faculty_name = trim($_POST['faculty_name']);

    // Validate inputs
    if (empty($new_faculty_id) || empty($new_faculty_name)) {
        $message = "<span class='error'>Faculty ID and Faculty Name are required.</span>";
    } else {
        // Use prepared statement to check if the faculty already exists
        $stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
        $stmt->bind_param("s", $new_faculty_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "<span class='error'>Faculty already exists with the same Faculty ID.</span>";
        } else {
            // Insert new faculty using prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO faculty (faculty_id, faculty_name) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $new_faculty_id, $new_faculty_name);

            if ($insert_stmt->execute()) {
                $message = "<span class='success'>New faculty added successfully.</span>";
            } else {
                $message = "<span class='error'>Error: " . htmlspecialchars($conn->error) . "</span>";
            }
        }
        $stmt->close();
    }
}

// Handle Delete Faculty
if (isset($_GET['delete_faculty'])) {
    $faculty_to_delete = $_GET['delete_faculty'];

    // Delete faculty using prepared statement
    $delete_stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_id = ?");
    $delete_stmt->bind_param("s", $faculty_to_delete);
    if ($delete_stmt->execute()) {
        $message = "<span class='success'>Faculty deleted successfully.</span>";
    } else {
        $message = "<span class='error'>Error: " . htmlspecialchars($conn->error) . "</span>";
    }
    $delete_stmt->close();
}

// Handle Add Department
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_department'])) {
    $selected_faculty_id = $_POST['selected_faculty_id'];
    $new_department_id = trim($_POST['department_id']);
    $new_department_name = trim($_POST['department_name']);

    // Validate inputs
    if (empty($new_department_id) || empty($new_department_name)) {
        $message = "<span class='error'>Department ID and Department Name are required.</span>";
    } else {
        // Use prepared statement to check if the department already exists under the selected faculty
        $stmt = $conn->prepare("SELECT * FROM department WHERE department_id = ? AND faculty_id = ?");
        $stmt->bind_param("ss", $new_department_id, $selected_faculty_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "<span class='error'>Department already exists with the same Department ID under this faculty.</span>";
        } else {
            // Insert new department using prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO department (department_id, department_name, faculty_id) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $new_department_id, $new_department_name, $selected_faculty_id);

            if ($insert_stmt->execute()) {
                $message = "<span class='success'>New department added successfully.</span>";
            } else {
                $message = "<span class='error'>Error: " . htmlspecialchars($conn->error) . "</span>";
            }
        }
        $stmt->close();
    }
}

// Handle Delete Department
if (isset($_GET['delete_department']) && isset($_GET['faculty_id'])) {
    $department_to_delete = $_GET['delete_department'];
    $associated_faculty_id = $_GET['faculty_id'];

    // Delete department using prepared statement
    $delete_stmt = $conn->prepare("DELETE FROM department WHERE department_id = ? AND faculty_id = ?");
    $delete_stmt->bind_param("ss", $department_to_delete, $associated_faculty_id);
    if ($delete_stmt->execute()) {
        $message = "<span class='success'>Department deleted successfully.</span>";
    } else {
        $message = "<span class='error'>Error: " . htmlspecialchars($conn->error) . "</span>";
    }
    $delete_stmt->close();
}

// Fetch existing faculties
$faculties_result = $conn->query("SELECT faculty_id, faculty_name FROM faculty");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty and Departments</title>
    <style>
        /* Reset some basic elements */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0fff0; /* Light green background */
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c662d; /* Dark green */
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.1em;
        }

        .success {
            color: #27ae60; /* Success green */
        }

        .error {
            color: #c0392b; /* Error red */
        }

        form {
            margin-bottom: 40px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c662d; /* Dark green */
        }

        input[type="text"], select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            transition: border-color 0.3s;
            font-size: 1em;
        }

        input[type="text"]:focus, select:focus {
            border-color: #27ae60; /* Focus green */
            outline: none;
        }

        input[type="submit"], .back-button, .manage-departments, .delete-button {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.3s, transform 0.2s;
        }

        input[type="submit"] {
            background-color: #27ae60; /* Green */
            color: #fff;
        }

        input[type="submit"]:hover {
            background-color: #1e8449; /* Darker green */
            transform: translateY(-2px);
        }

        .manage-departments {
            background-color: #16a085; /* Teal */
            color: #fff;
            margin-right: 10px;
        }

        .manage-departments:hover {
            background-color: #138d75; /* Darker teal */
            transform: translateY(-2px);
        }

        .delete-button {
            background-color: #c0392b; /* Red */
            color: #fff;
        }

        .delete-button:hover {
            background-color: #a93226; /* Darker red */
            transform: translateY(-2px);
        }

        .back-button {
            background-color: #95a5a6; /* Gray */
            color: #fff;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #7f8c8d; /* Darker gray */
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
            text-align: left;
        }

        th {
            background-color: #27ae60; /* Green header */
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:nth-child(even) {
            background-color: #e8f5e9; /* Light green */
        }

        tr:nth-child(odd) {
            background-color: #ffffff; /* White */
        }

        tr:hover {
            background-color: #d5f5e3; /* Hover green */
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 10px;
            }

            th, td {
                padding: 10px;
                font-size: 0.9em;
            }

            input[type="submit"], .back-button, .manage-departments, .delete-button {
                padding: 8px 16px;
                font-size: 0.9em;
            }
        }

        /* Additional Styling for Forms */
        form input[type="text"], form select {
            border-radius: 5px;
        }

        /* Button Hover Effects */
        a.delete-button, a.manage-departments, a.back-button {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
        }

        a.delete-button {
            background-color: #e74c3c; /* Red */
        }

        a.delete-button:hover {
            background-color: #c0392b; /* Darker red */
            transform: translateY(-2px);
        }

        a.manage-departments {
            background-color: #2ecc71; /* Green */
        }

        a.manage-departments:hover {
            background-color: #27ae60; /* Darker green */
            transform: translateY(-2px);
        }

        a.back-button {
            background-color: #95a5a6; /* Gray */
        }

        a.back-button:hover {
            background-color: #7f8c8d; /* Darker gray */
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Faculties</h2>

    <!-- Display messages -->
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Form to add new faculty -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="hidden" name="add_faculty" value="1">
        <label for="faculty_id">Faculty ID:</label>
        <input type="text" id="faculty_id" placeholder="Enter Faculty ID" name="faculty_id" required>
        
        <label for="faculty_name">Faculty Name:</label>
        <input type="text" id="faculty_name" placeholder="Enter Faculty Name" name="faculty_name" required>
        
        <input type="submit" value="Add Faculty">
    </form>

    <!-- Display existing faculties -->
    <h3>Existing Faculties</h3>
    <table>
        <tr>
            <th>Faculty ID</th>
            <th>Faculty Name</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($faculties_result->num_rows > 0) {
            // Output data of each row
            while ($row = $faculties_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row["faculty_id"]) . "</td>
                        <td>" . htmlspecialchars($row["faculty_name"]) . "</td>
                        <td>
                            <a class='manage-departments' href='?manage_departments=" . urlencode($row["faculty_id"]) . "'>Manage Departments</a>
                            <a class='delete-button' href='?delete_faculty=" . urlencode($row["faculty_id"]) . "' onclick='return confirm(\"Are you sure you want to delete this faculty and all its departments?\");'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No faculties found.</td></tr>";
        }
        ?>
    </table>

    <?php
    // If 'manage_departments' is set in GET, display departments for that faculty
    if (isset($_GET['manage_departments'])) {
        $current_faculty_id = $_GET['manage_departments'];

        // Fetch faculty details
        $faculty_stmt = $conn->prepare("SELECT faculty_name FROM faculty WHERE faculty_id = ?");
        $faculty_stmt->bind_param("s", $current_faculty_id);
        $faculty_stmt->execute();
        $faculty_result = $faculty_stmt->get_result();
        if ($faculty_result->num_rows > 0) {
            $faculty_row = $faculty_result->fetch_assoc();
            $faculty_name = $faculty_row['faculty_name'];
        } else {
            echo "<div class='message'><span class='error'>Faculty not found.</span></div>";
            exit;
        }
        $faculty_stmt->close();

        // Fetch departments under the current faculty
        $departments_stmt = $conn->prepare("SELECT department_id, department_name FROM department WHERE faculty_id = ?");
        $departments_stmt->bind_param("s", $current_faculty_id);
        $departments_stmt->execute();
        $departments_result = $departments_stmt->get_result();
    ?>
        <h2>Manage Departments for Faculty: <?php echo htmlspecialchars($faculty_name); ?> (<?php echo htmlspecialchars($current_faculty_id); ?>)</h2>

        <!-- Back button to return to faculties list -->
        <a class="back-button" href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">&#8592; Back to Faculties</a>

        <!-- Form to add new department -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <input type="hidden" name="add_department" value="1">
            <input type="hidden" name="selected_faculty_id" value="<?php echo htmlspecialchars($current_faculty_id); ?>">
            
            <label for="department_id">Department ID:</label>
            <input type="text" id="department_id" placeholder="Enter Department ID" name="department_id" required>
            
            <label for="department_name">Department Name:</label>
            <input type="text" id="department_name" placeholder="Enter Department Name" name="department_name" required>
            
            <input type="submit" value="Add Department">
        </form>

        <!-- Display existing departments -->
        <h3>Existing Departments under <?php echo htmlspecialchars($faculty_name); ?></h3>
        <table>
            <tr>
                <th>Department ID</th>
                <th>Department Name</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($departments_result->num_rows > 0) {
                // Output data of each row
                while ($dept_row = $departments_result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($dept_row["department_id"]) . "</td>
                            <td>" . htmlspecialchars($dept_row["department_name"]) . "</td>
                            <td>
                                <a class='delete-button' href='?delete_department=" . urlencode($dept_row["department_id"]) . "&faculty_id=" . urlencode($current_faculty_id) . "' onclick='return confirm(\"Are you sure you want to delete this department?\");'>Delete</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No departments found under this faculty.</td></tr>";
            }
            ?>
        </table>
    <?php
        $departments_stmt->close();
    }
    ?>

</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
