<?php
session_start(); // Start the session

// Check if the user is logged in as a Level Coordinator
if (!isset($_SESSION['staff'])) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit();
}

// Retrieve the name from the session
$coordName = htmlspecialchars($_SESSION['name']); // Coordinator's Name
$departmentName = htmlspecialchars($_SESSION['department_name']); // Department Name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level Coordinator Dashboard</title>
    <link rel="icon" type="images/x-icon" href="images/ausu.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome for icons -->
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #fff; /* white background for a cohesive look */
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px; /* Adjust based on the width of the leftbar */
            height: 100vh; /* Full height of the viewport */
            overflow: hidden; /* Prevent overflow */
        }
        .top-bar {
            position: relative;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: black;
        }
        .top-bar h1 {
            margin: 0;
            color: #fff;
        }
        .logout {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 18px;
            text-decoration: none;
            color: #fff;
            display: flex;
            align-items: center;
        }
        .logout i {
            margin-right: 5px;
        }
        iframe {
            width: 100%;
            height: calc(100% - 60px); /* Adjust height to fit under the top bar */
            border: none;
        }
    </style>
    <script>
        function loadPage(page) {
            document.getElementById('content-frame').src = page;
        }
    </script>
</head>
<body>
    <?php include("leftbar3.php"); ?>

    <div class="content">
        <div class="top-bar">
            <h1>Level Coordinator Dashboard</h1>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <!-- iframe to load the content -->
        <iframe src="manage_courses_levelcoord.php" id="content-frame"></iframe> <!-- Default content for Level Coordinator -->
    </div>

</body>
</html>
