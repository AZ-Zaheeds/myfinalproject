<?php 
session_start(); // Start the session

// Check if the student is logged in
if (!isset($_SESSION['student'])) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="icon" type="images/x-icon" href="images/ausu.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #f0f2f5;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px; /* Align with leftbar width */
            height: 100vh;
            overflow: hidden; 
        }
        .top-bar {
            position: relative;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
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
        iframe {
            width: 100%;
            height: calc(100% - 60px); /* Adjust height under top bar */
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
    <?php include("student_leftbar.php"); ?>

    <div class="content">
        <div class="top-bar">
            <h1>Student Dashboard</h1>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <iframe src="register_courses.php" id="content-frame"></iframe> <!-- Default content -->
    </div>
</body>
</html>
