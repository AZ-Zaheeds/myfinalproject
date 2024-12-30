<?php
// NewsFeed.php

// Start the session if needed (optional)
session_start();

// Include the database connection file
include 'dbconnection.php'; // Ensure this path is correct

// Fetch existing news feeds
$query = "SELECT title, content, created_at FROM news_feed ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Check for SQL errors
if (!$result) {
    die("Error in SQL query: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Feed</title>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-papKpys1g2D5Xn5pujm1IKJ1fVFz4E8HHe5bGZsUwSvz7hE1w5CF3x5ur0UaVOV9M6c9D2mrnAvlKum9O8duFw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet"href="css/newsfeed.css" >
    <link rel="icon" type="images/x-icon" href="images/ausu.ico">
    
    <script>
        // JavaScript to toggle content visibility and tick icon
        document.addEventListener('DOMContentLoaded', function() {
            const newsTitles = document.querySelectorAll('.news-item h3');

            newsTitles.forEach(function(title) {
                title.addEventListener('click', function() {
                    const newsItem = this.parentElement;
                    const content = this.nextElementSibling.nextElementSibling; // p tag

                    // Toggle active class
                    newsItem.classList.toggle('active');
                });
            });
        });
    </script>
</head>
<body>

<div class="newsfeed-container">
    <h1>News Feed</h1>

    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Generate unique IDs for each news content if needed
            echo "<div class='news-item'>";
            echo "<h3>" . htmlspecialchars($row['title']) . " <i class='fas fa-check-double tick-icon'></i></h3>";
            echo "<div class='date'>" . date('F j, Y, g:i a', strtotime($row['created_at'])) . "</div>";
            echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='no-news'>No news feeds available.</div>";
    }
    ?>
</div>

</body>
</html>
