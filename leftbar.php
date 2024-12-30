<?php
// Assuming you have already started the session
$adminName = isset($_SESSION['adminName']) ? $_SESSION['adminName'] : 'Admin';

// Profile image URL (You can replace this with a dynamic image URL if needed)
$profileImage = "images/admin.png"; // Replace with actual image path
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Leftbar</title>
    <link rel="stylesheet" href="css/leftbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="leftbar">
    <div class="profile">
        <img src="<?php echo $profileImage; ?>" alt="Profile Image">
    </div>
    <div class="welcome">
        Welcome, <span class="admin-name"><?php echo htmlspecialchars($adminName); ?></span>
    </div>

    <div class="nav">
        <!-- Update the links to use loadPage function with Font Awesome icons -->
        <a href="#" onclick="loadPage('manage_faculty.php'); return false;">
            <i class="fas fa-university"></i> Manage Faculty
        </a>
        <a href="#" onclick="loadPage('manage_super_admins.php'); return false;">
            <i class="fas fa-user-shield"></i> Manage Super Admins
        </a>
        <a href="#" onclick="loadPage('manage_hod.php'); return false;">
            <i class="fas fa-user-tie"></i> HOD Management
        </a>
    </div>
</div>

</body>
</html>
