<?php
session_start();

// Check if the user is logged in as HOD and has a department_id
if (!isset($_SESSION['staff']) || !isset($_SESSION['department_id'])) {
    header("Location: index.php");
    exit();
}

$department_id = $_SESSION['department_id']; // Retrieve department ID from session

include 'dbconnection.php'; // Ensure this file connects to your database

// Initialize message variable
$message = "";

// Handle CSV Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_csv'])) {
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['csv_file']['tmp_name'];
        $file_name = $_FILES['csv_file']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if ($file_ext === 'csv') {
            // Open the CSV file
            if (($handle = fopen($file_tmp, 'r')) !== FALSE) {
                fgetcsv($handle); // Skip header row
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $student_name = mysqli_real_escape_string($conn, $data[0]);
                    $registration_number = mysqli_real_escape_string($conn, $data[1]);
                    $student_password = mysqli_real_escape_string($conn, $data[2]); // Plain text password, no hash
                    $email = mysqli_real_escape_string($conn, $data[3]);
                    $faculty_id = mysqli_real_escape_string($conn, $data[4]);
                    $level = intval(mysqli_real_escape_string($conn, $data[5])); // Ensure level is an integer

                    // Check if registration number or email already exists
                    $check_student = mysqli_query($conn, "SELECT * FROM student WHERE registration_number = '$registration_number' OR email = '$email'");
                    if (mysqli_num_rows($check_student) == 0) {
                        // Use prepared statements for security
                        $stmt = $conn->prepare("INSERT INTO student (name, registration_number, password, email, department_id, faculty_id, level) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssssi", $student_name, $registration_number, $student_password, $email, $department_id, $faculty_id, $level);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $message .= "Error: Student with registration number $registration_number or email $email already exists!<br>";
                    }
                }
                fclose($handle);
                $message .= "Students uploaded successfully!";
            } else {
                $message = "Error opening the CSV file.";
            }
        } else {
            $message = "Please upload a valid CSV file.";
        }
    } else {
        $message = "Error uploading file.";
    }
}

// Handle Delete Student
if (isset($_GET['delete_student'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['delete_student']);
    // Use prepared statements for security
    $stmt = $conn->prepare("DELETE FROM student WHERE id = ? AND department_id = ?");
    $stmt->bind_param("is", $student_id, $department_id);

    if ($stmt->execute()) {
        $message = "Student deleted successfully!";
    } else {
        $message = "Error deleting student: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch Students from the same department as the logged-in HOD
$stmt = $conn->prepare("SELECT * FROM student WHERE department_id = ?");
$stmt->bind_param("s", $department_id);
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard - Manage Students</title>
    <link rel="stylesheet" href="css/hod_dashboard.css"> <!-- External CSS -->
    <style>
        /* Additional styling for the page */
        #editStudentForm {
            display: none; /* Initially hidden, shown when editing */
            background-color: #fff;
            padding: 20px;
            margin-top: 20px;
            border: 2px solid #007bff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
    <script>
        // Function to show specific sections
        function showSection(sectionId) {
            var sections = document.querySelectorAll('form, table');
            sections.forEach(function(section) {
                section.style.display = 'none'; // Hide all sections
            });

            var section = document.getElementById(sectionId);
            if (section) {
                section.style.display = 'block'; // Show the selected section
            }
        }
    </script>
</head>
<body>
    <main>
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Manage Students Section -->
        <section id="manage_students">
            <h2>Manage Students</h2>
            <div class="section-links">
                <a href="#" onclick="showSection('csvUpload')">Add Students</a>
                <a href="#" onclick="showSection('studentTable')">View Students</a>
            </div>

            <!-- CSV Upload Section -->
            <form id="csvUpload" method="POST" action="" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit" name="upload_csv">Upload CSV</button>
            </form>

            <!-- Table to display students -->
            <table id="studentTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Registration Number</th>
                        <th>password</th>
                        <th>Email</th>
                        <th>Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['password']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['level']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        
    </footer>
</body>
</html>
