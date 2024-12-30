<?php  
session_start();

// Check if the user is logged in as HOD and has a department_id
if (!isset($_SESSION['staff']) || !isset($_SESSION['department_id'])) {
    header("Location: index.php");
    exit();
}

$department_id = $_SESSION['department_id']; // Retrieve department ID from session

include 'dbconnection.php';

// Initialize message variable
$message = "";

// Handle Level Coordinator Management
// Add Level Coordinator
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_coordinator'])) {
    // Retrieve and sanitize form inputs
    $coordinator_name = mysqli_real_escape_string($conn, $_POST['coordinator_name']);
    $coordinator_username = mysqli_real_escape_string($conn, $_POST['coordinator_username']);
    $coordinator_password = mysqli_real_escape_string($conn, $_POST['coordinator_password']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);

    // Check if username already exists
    $check_username = mysqli_query($conn, "SELECT * FROM staff_2 WHERE username = '$coordinator_username'");
    if(mysqli_num_rows($check_username) > 0){
        $message = "Error: Username already exists!";
    } else {
            
        // Use prepared statements for security
        $stmt = $conn->prepare("INSERT INTO staff_2 (name, username, password, department_id, level) VALUES (?, ?, ?, ?, ?)");
    $coordinator_password = mysqli_real_escape_string($conn, $_POST['coordinator_password']);
        $stmt->bind_param("sssss", $coordinator_name, $coordinator_username, $coordinator_password, $department_id, $level);

        if ($stmt->execute()) {
            $message = "Level Coordinator added successfully!";
        } else {
            $message = "Error adding coordinator: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Delete Level Coordinator
if (isset($_GET['delete_coordinator'])) {
    $coordinator_id = mysqli_real_escape_string($conn, $_GET['delete_coordinator']);
    // Use prepared statements for security
    $stmt = $conn->prepare("DELETE FROM staff_2 WHERE id = ?");
    $stmt->bind_param("i", $coordinator_id);

    if ($stmt->execute()) {
        $message = "Level Coordinator deleted successfully!";
    } else {
        $message = "Error deleting coordinator: " . $stmt->error;
    }

    $stmt->close();
}

// Handle Edit Coordinator
$edit_coordinator = null;
if (isset($_GET['edit_coordinator'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_coordinator']);
    
    // Fetch the coordinator's current data
    $stmt = $conn->prepare("SELECT * FROM staff_2 WHERE id = ? AND department_id = ?");
    $stmt->bind_param("ii", $edit_id, $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $edit_coordinator = $result->fetch_assoc();
    } else {
        $message = "Coordinator not found or you do not have permission to edit.";
    }
    
    $stmt->close();
}

// Handle Update Coordinator
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_coordinator'])) {
    $update_id = mysqli_real_escape_string($conn, $_POST['coordinator_id']);
    $new_level = mysqli_real_escape_string($conn, $_POST['level']);
    
    // Update the coordinator's level
    $stmt = $conn->prepare("UPDATE staff_2 SET level = ? WHERE id = ? AND department_id = ?");
    $stmt->bind_param("iii", $new_level, $update_id, $department_id);
    
    if ($stmt->execute()) {
        $message = "Coordinator level updated successfully!";
    } else {
        $message = "Error updating coordinator: " . $stmt->error;
    }
    
    $stmt->close();
    
    // Refresh the coordinators list after update
    header("Location: manage_coordinators.php"); // Replace with your actual file name
    exit();
}

// Fetch Level Coordinators
$stmt = $conn->prepare("SELECT * FROM staff_2 WHERE  department_id = ?");
$stmt->bind_param("s",  $department_id);
$stmt->execute();
$coordinators = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard - Manage Coordinators</title>
    <link rel="stylesheet" href="css/hod_dashboard.css"> <!-- External CSS -->
    <style>
        /* Reset some basic elements */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }

        header {
            background-color: #28a745; /* Green */
            color: white;
            padding: 20px 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        header h1 {
            margin-bottom: 10px;
            font-size: 1.8em;
        }

        header h3 {
            margin-bottom: 10px;
            font-weight: normal;
            font-size: 1.2em;
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        nav ul li {
            display: inline;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background-color: #218838; /* Darker green */
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
        }

        nav ul li a:hover {
            background-color: #1e7e34;
        }

        /* Alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            font-size: 1em;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        main {
            max-width: 1200px;
            margin: 0 auto;
        }

        section {
            background-color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        section h2 {
            margin-bottom: 15px;
            border-bottom: 2px solid #28a745; /* Green underline */
            padding-bottom: 5px;
            color: #28a745;
        }

        .section-links {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .section-links a {
            background-color: #17a2b8; /* Teal */
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
            cursor: pointer;
        }

        .section-links a:hover {
            background-color: #138496;
        }

        /* Form styling */
        form {
            display: none;
            flex-direction: column;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form textarea,
        form select {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        form textarea {
            resize: vertical;
            height: 100px;
        }

        form button {
            padding: 10px;
            background-color: #28a745; /* Green */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #218838;
        }

        /* Table styling */
        table {
            display: none;
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 0.95em;
        }

        table th {
            background-color: #28a745; /* Green header */
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .delete-button {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .delete-button:hover {
            color: #c82333;
            text-decoration: underline;
        }

        .edit-button {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
            transition: color 0.3s ease;
        }

        .edit-button:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: #28a745; /* Green */
            color: white;
            border-radius: 5px;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                align-items: center;
            }

            .section-links {
                flex-direction: column;
                align-items: stretch;
            }

            .section-links a {
                text-align: center;
            }
        }

        /* Additional styling for the Edit Coordinator Form */
        #editCoordinatorForm {
            display: none; /* Initially hidden, shown when editing */
            background-color: #fff;
            padding: 20px;
            margin-top: 20px;
            border: 2px solid #007bff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        #editCoordinatorForm h3 {
            margin-bottom: 15px;
            color: #007bff;
        }
    </style>
    <script>
        // Function to show specific sections
        function showSection(sectionId) {
            var sections = document.querySelectorAll('form, table');
            sections.forEach(function(section) {
                section.style.display = 'none'; // Hide all sections
            });

            var section = document.getElementById(sectionId);
            if(section){
                section.style.display = 'block'; // Show the selected section
            }
        }

        // Show the edit form if editing a coordinator
        window.onload = function() {
            <?php if ($edit_coordinator): ?>
                showSection('editCoordinatorForm');
            <?php endif; ?>
        };
    </script>
    <?php 
    if (!empty($message)) { 
        // Differentiate message types (success vs error)
        $class = strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success';
        echo "<div class='alert $class'>{$message}</div>"; 
    } 
    ?>
</head>
<body>
    
    <main>
        <!-- Manage Level Coordinators Section -->
        <section id="manage_coordinators">
            <h2>Manage Level Coordinators</h2>
            <div class="section-links">
                <a href="#" onclick="showSection('coordinatorForm')">Add Coordinator</a>
                <a href="#" onclick="showSection('coordinatorTable')">View Coordinators</a>
            </div>
            <!-- Form to add a level coordinator -->
            <form id="coordinatorForm" method="POST" action="">
                <input type="text" name="coordinator_name" placeholder="Coordinator Name" required>
                <input type="text" name="coordinator_username" placeholder="Coordinator Username" required>
                <input type="password" name="coordinator_password" placeholder="Coordinator Password" required>
                <select name="level" required>
                    <option value="">Select Level</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                <button type="submit" name="add_coordinator">Add Coordinator</button>
            </form>

            <!-- Edit Coordinator Form -->
            <?php if ($edit_coordinator): ?>
                <form id="editCoordinatorForm" method="POST" action="">
                    <h3>Edit Coordinator Level</h3>
                    <input type="hidden" name="coordinator_id" value="<?php echo htmlspecialchars($edit_coordinator['id']); ?>">
                    <label for="level">Select New Level:</label>
                    <select name="level" required>
                        <option value="">Select Level</option>
                        <option value="100" <?php if($edit_coordinator['level'] == 100) echo 'selected'; ?>>100</option>
                        <option value="200" <?php if($edit_coordinator['level'] == 200) echo 'selected'; ?>>200</option>
                        <option value="300" <?php if($edit_coordinator['level'] == 300) echo 'selected'; ?>>300</option>
                        <option value="400" <?php if($edit_coordinator['level'] == 400) echo 'selected'; ?>>400</option>
                        <option value="500" <?php if($edit_coordinator['level'] == 500) echo 'selected'; ?>>500</option>
                    </select>
                    <button type="submit" name="update_coordinator">Update Level</button>
                    <!-- Optionally, add a cancel button to stop editing -->
                    <button type="button" onclick="window.location.href='manage_coordinators.php';">Cancel</button>
                </form>
            <?php endif; ?>

            <!-- Existing Level Coordinators Table -->
            <table id="coordinatorTable">
                <thead>
                    <tr>
                      <!--<th>ID</th>-->
                        <th>Name</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Level</th>
                        <th>Action</th> <!-- New Action Column -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($coordinator = mysqli_fetch_assoc($coordinators)): ?>
                        <tr>
                           <!-- <td><?php echo htmlspecialchars($coordinator['id']); ?></td>-->
                            <td><?php echo htmlspecialchars($coordinator['name']); ?></td>
                            <td><?php echo htmlspecialchars($coordinator['username']); ?></td>
                            <td><?php echo htmlspecialchars($coordinator['password']); ?></td>
                            <td><?php echo htmlspecialchars($coordinator['level']); ?></td>
                            <td>
                                <a href="?delete_coordinator=<?php echo urlencode($coordinator['id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this coordinator?');">Delete</a>
                                <a href="?edit_coordinator=<?php echo urlencode($coordinator['id']); ?>" class="edit-button">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
       
    </footer>
</body>
</html>

<?php
mysqli_close($conn);
?>
