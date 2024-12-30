<?php
// Database connection settings
$host = "localhost";    // Host (usually localhost for local development)
$username = "root";     // MySQL username (use 'root' if you're using XAMPP or WAMP without changing credentials)
$password = "";         // MySQL password (by default, it’s empty for XAMPP)
$database = "onlinecr"; // Your database name (replace with your actual database)

// Create a connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
//echo "Connected successfully!";

