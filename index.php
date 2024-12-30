<?php   
session_start();
include 'dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a staff login or student login
    if (isset($_POST['username'])) {
        // Staff login (same code you already provided)
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $password = mysqli_real_escape_string($conn, trim($_POST['password']));

        // Check admin table
        $admin_sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
        $admin_result = mysqli_query($conn, $admin_sql);

        if (mysqli_num_rows($admin_result) == 1) {
            // Admin login success
            $admin_row = mysqli_fetch_assoc($admin_result);
            $_SESSION['superadmin'] = $admin_row['username'];
            $_SESSION['adminName'] = $admin_row['name'];
            header("Location: superadmin_dashboard.php");
            exit();
        } else {
            // Check HOD table
            $hod_sql = "SELECT s.*, d.department_name FROM staff_1 s 
                        JOIN department d ON s.department_id = d.department_id
                        WHERE s.username = '$username' AND s.password = '$password'";
            $hod_result = mysqli_query($conn, $hod_sql);

            if (mysqli_num_rows($hod_result) == 1) {
                // HOD login success
                $hod_row = mysqli_fetch_assoc($hod_result);
                $_SESSION['staff'] = $hod_row['username'];
                $_SESSION['name'] = $hod_row['name'];
                $_SESSION['department_id'] = $hod_row['department_id'];
                $_SESSION['department_name'] = $hod_row['department_name'];
                header("Location: hod_dashboard.php");
                exit();
            } else {
                // Check level coordinator staff_2 table
                $staff2_sql = "SELECT s.*, d.department_name FROM staff_2 s 
                              JOIN department d ON s.department_id = d.department_id
                              WHERE s.username = '$username' AND s.password = '$password'";
                $staff2_result = mysqli_query($conn, $staff2_sql);

                if (mysqli_num_rows($staff2_result) == 1) {
                    // Staff_2 login success
                    $staff2_row = mysqli_fetch_assoc($staff2_result);
                    $_SESSION['staff'] = $staff2_row['username'];
                    $_SESSION['name'] = $staff2_row['name'];
                    $_SESSION['department_id'] = $staff2_row['department_id'];
                    $_SESSION['department_name'] = $staff2_row['department_name'];
                    $_SESSION['level'] = $staff2_row['level'];
                    header("Location: levelcoord_dashboard.php");
                    exit();
                } else {
                    // Check lecturer staff_3 table
                    $staff3_sql = "SELECT s.*, d.department_name FROM staff_3 s 
                                  JOIN department d ON s.department_id = d.department_id
                                  WHERE s.username = '$username' AND s.password = '$password'";
                    $staff3_result = mysqli_query($conn, $staff3_sql);

                    if (mysqli_num_rows($staff3_result) == 1) {
                        // Staff_3 login success
                        $staff3_row = mysqli_fetch_assoc($staff3_result);
                        $_SESSION['staff'] = $staff3_row['username'];
                        $_SESSION['name'] = $staff3_row['name'];
                        $_SESSION['department_id'] = $staff3_row['department_id'];
                        $_SESSION['department_name'] = $staff3_row['department_name'];
                        $_SESSION['level'] = $staff3_row['level'];
                        header("Location: lecturer_dashboard.php");
                        exit();
                    } else {
                        // Invalid login for admin, HOD, staff_2, and staff_3
                        echo "<script>
                                alert('Invalid username or password');
                                window.location.href='index.php';
                              </script>";
                    }
                }
            }
        }
    } elseif (isset($_POST['regno'])) {
        // Student login
        $regno = mysqli_real_escape_string($conn, trim($_POST['regno']));
        $password2 = mysqli_real_escape_string($conn, trim($_POST['password2']));

        // Check students table
        $student_sql = "SELECT * FROM student WHERE registration_number = '$regno' AND password = '$password2'";
        $student_result = mysqli_query($conn, $student_sql);

        if (mysqli_num_rows($student_result) == 1) {
            // Student login success
            $student_row = mysqli_fetch_assoc($student_result);
            $_SESSION['student'] = $student_row['registration_number'];
            $_SESSION['name'] = $student_row['name'];
            $_SESSION['department_id'] = $student_row['department_id'];
            $_SESSION['level'] = $student_row['level'];
            $_SESSION['department_name'] = $student_row['department_name'];

            // Redirect to student dashboard (adjust to your actual page)
            header("Location: student_dashboard.php");
            exit();
        } else {
            // Invalid login for students
            echo "<script>
                    alert('Invalid registration number or password');
                    window.location.href='index.php';
                  </script>";
        }
    }
    mysqli_close($conn);
}
?>



<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Online Course Registration</title>
        <link rel="icon" type="images/x-icon" href="images/ausu.ico">
        <link rel="stylesheet" href="css/my1.css">
        <link rel="stylesheet" href="css/my2.css">
        <style>
            
        </style>
    </head>
    <body>
        <nav>
            <div>
                <img src="images/A.jpg" alt="Home" >
            </div>    
                <ul>
                <li id="sl"><a id="sl1" href="#" onclick="showStudentContainer(event)">Students Login</a></li>
                <li id="al"><a id="sl2" href="#" onclick="showStaffContainer(event)">Staff Login</a></li>
                <li id="nf"><a id="sl3" href="NewsFeed.php">News Feed</a></li>
                </ul>  
     <!-- Container for both staff selection and login form -->
     <div id="staff-container" class="login-container">
        <h3 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">Staff login:</h3>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <input type="email" name="username" placeholder="Enter email" required><br>
                        <input type="password" name="password" placeholder="Password" required> <br>
                        <button type="submit">Login</button>
                    </form>

        <!-- This is where the login form will appear -->
        <div id="login-form" style="margin-top: 20px;"></div>
    </div>
    
    
    <div id="student-container" class="login-container2">
        <h3 style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">Student login:</h3>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <input type="text" name="regno" placeholder="Enter registration number." 
                        pattern="[A-Za-z]{3}/[A-Za-z]{3}/[A-Za-z]{3}/\d{2}/\d{4}" 
                        required title="Please enter a valid format, e.g., AUS/SCC/SFE/20/1003" required><br>
                        <input type="password" name="password2" placeholder="Password" required <br>
                        <button type="submit">Login</button>
                    </form>

        <!-- This is where the login form will appear -->
        <div id="login-form" style="margin-top: 20px;"></div>
    </div>


    
        </nav>
        
        <section>
            <h1 class="learntocodeh1">Online Course Registration</h1>
            <h3 class="learntocodeh3">AL-istiqama University, Sumaila</h3>
            <div class="container">
                  <div class="typewriter" id="typewriter">
                 </div>
            </div>
            
           
        </section>
        <footer>
            <div class="social-links">
                <a href="https://wa.me/+2348146111936" target="_blank">
                    <img src="images/w.jpeg" alt="whatsApp">
                </a>
                <a href="https://www.facebook.com/Zaheed umar" target="_blank">
                    <img src="images/f.png" alt="facebook">
                </a>
                <a href="https://www.instagram.com/Zaheedumar" target="_blank">
                    <img src="images/i.jpeg" alt="instagram">
                </a>
            </div>
            <p>Al-Istiqama University online course registration system, is optimized to improve the 
                course registration system in the university. While using this site, you 
               agree to have read and accepted our <a href="policyrights.html" style="color: #ffffff; ">term of
               use</a><br> &copy;2024 Shazadeen 
               technologies. All right reserved</p>
        </footer>
        <script src="javascrpt/typewriter.js"></script>
        <script src="javascrpt/hiddencontainer.js"></script>
        
        </body>
</html>