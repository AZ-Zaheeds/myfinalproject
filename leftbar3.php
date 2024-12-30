<?php
// Ensure the user is logged in as a staff member
if (!isset($_SESSION['staff'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch stored session variables
$coordName = htmlspecialchars($_SESSION['name']); // Coordinator's Name
$departmentName = htmlspecialchars($_SESSION['department_name']); // Department Name
$level = $_SESSION['level']; // Level being managed
// Profile image URL (replace with dynamic path if needed)
$profileImage = "images/admin.png"; // Ensure this path is correct
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>level Coordinator Sidebar</title>
    <link rel="stylesheet" href="css/leftbar2.css"> <!-- Link to external CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<!-- Sidebar Styles -->
<div style="width: 250px; height: 100vh; background-color: black; color: white; position: fixed;">
<div class="profile">
            <img src="<?php echo $profileImage; ?>" alt="Profile Image"> <!-- Replace with actual image path if needed -->
        </div>
    <div style="padding: 20px;">
        <h2>Welcome, <?php echo $coordName; ?></h2>
        <p><strong>Department:</strong> <?php echo $departmentName; ?></p>
        <p><strong>Level:</strong> <?php echo $level; ?></p>
    </div>
    <!-- Navigation Links -->
    <ul style="list-style: none; padding: 0; margin: 0;">
        <li style="padding: 10px 20px;">
            <a href="javascript:void(0);" onclick="loadPage('manage_courses_levelcoord.php')" style="color: white; text-decoration: none;">
                <i class="fas fa-book"></i> Manage Courses
            </a>
        </li>
        <li style="padding: 10px 20px;">
            <a href="javascript:void(0);" onclick="loadPage('View_students.php')" style="color: white; text-decoration: none;">
                <i class="fas fa-users"></i> View Students
            </a>
        </li>
       
        <li style="padding: 10px 20px;">
            <a href="javascript:void(0);" onclick="loadPage('Students_CRF.php')" style="color: white; text-decoration: none;">
                <i class="fas fa-file-alt"></i> View Students CRF
            </a>
        </li>
        <li style="padding: 10px 20px;">
            <a href="javascript:void(0);" onclick="loadPage('add_news.php')" style="color: white; text-decoration: none;">
                <i class="fas fa-newspaper"></i> Add News
            </a>
        </li>
    </ul>
</div>
</body>
</html>