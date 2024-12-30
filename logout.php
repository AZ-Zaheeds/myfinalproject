<?php
session_start(); // Start the session

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: index.php"); // Change to your login page if necessary
exit(); // Ensure no further code runs after redirect
?>
