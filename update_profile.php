<?php
session_start();
require_once "dbconnection.php"; // Ensure this file connects to your MySQL database

// Check if the student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: index.php");
    exit();
}

// Fetch session details
$registrationNumber = $_SESSION['student']; // Student's registration number (unique)
//$studentName = $_SESSION['name'] ?? 'Student';
$studentName = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; // Student Name
//$currentLevel = $_SESSION['level'] ?? 'Not Assigned';
$currentLevel = isset($_SESSION['level']) ? htmlspecialchars($_SESSION['level']) : 'Not Assigned'; // Level
// Variables for success/error messages
$successMessage = '';
$errorMessage = '';

// Handle level update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['new_level'])) {
        $newLevel = htmlspecialchars($_POST['new_level']);

        // Update query for the 'student' table
        $updateQuery = "UPDATE student SET level = ? WHERE registration_number = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ss", $newLevel, $registrationNumber);

        if ($stmt->execute()) {
            $successMessage = "Level updated successfully!";
            $_SESSION['level'] = $newLevel; // Update level in session
            $currentLevel = $newLevel;
        } else {
            $errorMessage = "Error updating level. Try again.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Please select a valid level.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
/* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fc;
    color: #333;
    line-height: 1.6;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    background: #ffffff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
    text-align: center;
}

h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #5a2a83;
    font-weight: 600;
}

p {
    font-size: 16px;
    color: #555;
    margin-bottom: 20px;
}

strong {
    color: #333;
    font-weight: 600;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

label {
    font-size: 16px;
    color: #333;
    text-align: left;
}

select {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #fafafa;
    transition: border-color 0.3s ease;
}

select:focus {
    border-color: #5a2a83;
    outline: none;
}

button {
    padding: 10px 20px;
    background-color: #5a2a83;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #4a1c66;
}

a {
    font-size: 16px;
    color: #5a2a83;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
    transition: color 0.3s ease;
}

a:hover {
    color: #4a1c66;
}

div {
    margin-top: 10px;
}

div[style="color: green;"] {
    color: green;
}

div[style="color: red;"] {
    color: red;
}

i {
    margin-right: 5px;
}


    </style>


</head>
<body>
<div class="container">
    <h2>Update Your Level</h2>
    <p>Welcome, <strong><?php echo $studentName; ?></strong> (Reg No: <?php echo $registrationNumber; ?>)</p>
    <p>Current Level: <strong><?php echo $currentLevel; ?></strong></p>

    <!-- Display success or error message -->
    <?php if ($successMessage): ?>
        <div style="color: green;"><?php echo $successMessage; ?></div>
    <?php elseif ($errorMessage): ?>
        <div style="color: red;"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <!-- Form to update level -->
    <form method="POST" action="update_profile.php">
        <label for="new_level">Select New Level:</label>
        <select name="new_level" id="new_level" required>
            <option value="" disabled selected>Select your level</option>
            <option value="100" <?php if ($currentLevel == '100') echo 'selected'; ?>>100</option>
            <option value="200" <?php if ($currentLevel == '200') echo 'selected'; ?>>200</option>
            <option value="300" <?php if ($currentLevel == '300') echo 'selected'; ?>>300</option>
            <option value="400" <?php if ($currentLevel == '400') echo 'selected'; ?>>400</option>
            <option value="500" <?php if ($currentLevel == '500') echo 'selected'; ?>>500</option>
        </select>
        <br><br>
        <button type="submit"><i class="fas fa-save"></i> Update Level</button>
    </form>

</div>
</body>
</html>
