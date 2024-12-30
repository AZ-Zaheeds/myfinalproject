<?php


// Check if the student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch student details from the session
$departmentName = isset($_SESSION['department_id']) ? htmlspecialchars($_SESSION['department_id']) : 'Unknown'; // Department Name
$studentName = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; // Student Name
$level = isset($_SESSION['level']) ? htmlspecialchars($_SESSION['level']) : 'Not Assigned'; // Level
$registrationNumber = isset($_SESSION['student']) ? htmlspecialchars($_SESSION['student']) : 'N/A'; // Registration Number
$profileImage = "images/student.jpeg"; // Replace with the actual image path
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Leftbar</title>
    <link rel="stylesheet" href="css/leftbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="leftbar">
    <div class="profile">
        <!-- Display the student's profile image -->
        <img src="<?php echo $profileImage; ?>" alt="Profile Image">
    </div>
    <div class="welcome">
        <p>Welcome, <span class="student-name"><?php echo $studentName; ?></span></p>
        <p>Department: <strong><?php echo $departmentName; ?></strong></p>
        <p>Level: <strong><?php echo $level; ?></strong></p>
        <p>Reg. No: <strong><?php echo $registrationNumber; ?></strong></p>
    </div>

    <div class="nav">
        <a href="#" onclick="loadPage('register_courses.php'); return false;">
            <i class="fas fa-book"></i> Courses
        </a>
        <a href="#" onclick="loadPage('my_crf.php'); return false;">
            <i class="fas fa-chart-line"></i> View CRF
        </a>
        <a href="#" onclick="loadPage('update_profile.php'); return false;">
            <i class="fas fa-user-edit"></i> Update Profile
        </a>
    </div>
</div>

</body>
</html>
