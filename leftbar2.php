<?php 
// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as HOD
if (isset($_SESSION['staff'])) {
    $hodName = htmlspecialchars($_SESSION['name']); // HOD's Name
    $departmentName = htmlspecialchars($_SESSION['department_name']); // Department Name
} else {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit();
}

// Profile image URL (replace with dynamic path if needed)
$profileImage = "images/admin.png"; // Ensure this path is correct
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Sidebar</title>
    <link rel="stylesheet" href="css/leftbar2.css"> <!-- Link to external CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="leftbar">
        <div class="profile">
            <img src="<?php echo $profileImage; ?>" alt="Profile Image"> <!-- Replace with actual image path if needed -->
        </div>
        <div class="welcome">
            Welcome, <span class="admin-name"><?php echo $hodName; ?></span> 
        </div>
        
        <div class="department">
            <strong>Department: <?php echo $departmentName; ?></strong>
        </div>
        <div class="nav">
            <a href="#" onclick="loadPage('manage_courses.php'); return false;">
                <i class="fas fa-book"></i> Manage Courses
            </a>
            <a href="#" onclick="loadPage('manage_coordinators.php'); return false;">
                <i class="fas fa-users"></i> Manage Level Coordinators
            </a>
            <a href="#" onclick="loadPage('assign_lecturers.php');return false;">
                <i class="fas fa-chalkboard-teacher"></i> Manage Lecturers
            </a>
            <a href="#" onclick="loadPage('upload_students.php'); return false;">
                <i class="fas fa-upload"></i> Upload Students
            </a>
            <a href="#" onclick="loadPage('add_news.php'); return false;">
                <i class="fas fa-newspaper"></i> Add News Feed
            </a>
        </div>
    </div>
</body>
</html>
