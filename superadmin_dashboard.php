<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['superadmin'])) {
    // Redirect to login page if not logged in
    header("Location: index.php"); // Change to your login page if necessary
    exit();
}

// Retrieve the name from the session (instead of username)
$adminName = $_SESSION['superadmin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link rel="icon" type="images/x-icon" href="images/ausu.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome for icons -->
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #282a35;
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
            background-color: white;
        }
        .top-bar h1 {
            margin: 0;
        }
        .logout {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 18px;
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
        }
        .logout i {
            margin-right: 5px;
        }
        .leftbar {
            position: fixed; /* Keep the leftbar fixed */
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh; /* Full height of the browser */
            background-color: #f7f7f7;
            padding: 20px;
            box-shadow: 2px 0px 5px rgba(0,0,0,0.1);
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
    <?php include("leftbar.php");?>

    <div class="content">
        <div class="top-bar">
            <h1>Superadmin Dashboard</h1>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <!-- iframe to load the content -->
        <iframe src="manage_faculty.php" id="content-frame"></iframe> <!-- Default content -->
    </div>

</body>
</html>
