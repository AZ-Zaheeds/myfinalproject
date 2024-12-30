<?php
$error = "";
$success = "";
$success_data ="";
// if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    if($_FILES["upload_csv"]["error"] == 4) {
        $error.="<li>Please select csv file to upload.</li>";
    }else{
        $file_path = pathinfo($_FILES['upload_csv']['name']);
        $file_ext = $file_path['extension'];
        $file_tmp = $_FILES['upload_csv']['tmp_name'];
        $file_size = $_FILES['upload_csv']['size'];  
        // CSV file extension validation
        if ($file_ext != "csv"){
            $error.="<li>Sorry, only csv file format is allowed.</li>";
          }
        // 1MB file size validation
        if ($file_size > 1048576) {
            $error.="<li>Sorry, maximum 1 MB file size is allowed.</li>";
          }
        if(empty($error)){
            // Number of rows in CSV validation (3 rows are allowed for now)
            $file_rows = file($file_tmp);
            if(count($file_rows) > 3){
                $error.="<li>Sorry, you can upload maximum 3 rows of data in one go.</li>";
            }
        }
    }
    // if there is no error, then import CSV data into MySQL Database
    if(empty($error)){
        // Include the database connection file 
        require_once 'config.php';
        //$db = new $con;
        $file = fopen($file_tmp, "r");
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== FALSE) {

             // Get row data
            $reg = $row[0];
            $name = $row[1];
            $fac = $row[2];
            $dpt = $row[3];
            $email = $row[4];
            
            //query("INSERT INTO 'users' ( 'name', 'email') VALUES (:name, :email)");
            //mysqli_query($con, "INSERT INTO stu (name, email) VALUES ('$name', '$email') ");
    //mysqli_query($con, "INSERT INTO users (name, email, create_at, update_at) VALUES ('" . $name . "', '" . $email . "','nill', NOW())");
    //mysqli_query($con, "INSERT INTO stu (regno, name, faculty, dept, email, phone, ppa_name, address, password) VALUES ('" . $reg . "', '" . $name . "', '" . $fac . "', '" . $dpt . "', '" . $email . "', '" . $phn . "', '" . $ppa . "', '" .$address."','".$password . "',)");
    mysqli_query($con, "INSERT INTO stu (regno, name, faculty, dept, email) VALUES ('" . $reg . "', '" . $name . "', '" . $fac . "', '" . $dpt . "', '" . $email . "')");
    
            // Insert csv data into the `import_csv_data` database table
            //$db->query("INSERT INTO 'users' ( 'name', 'email') VALUES (:name, :email)");
            //$db->bind(":id", $row[0]);
            //$db->bind(":name", $row[0]);
            //$db->bind(":email", $row[1]);
            //$db->execute();
            $success_data .= "<li>".$row[0]." ".$row[1]." ".$row[2]." ".$row[3]." ".$row[4]." "."</li>";
        }
        fclose($file);
        //$db->close();
        $success = "Following CSV data is imported successfully.";
    }
}
?>
<html>
<head>
<title></title>
<link rel='stylesheet' href='css/style.css' type='text/css' media='all' />
<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">

<style type="text/css">
    body {
  font-family: Arial, sans-serif;
  line-height: 1.6;
}
input[type=submit] {
  font-family: Arial, sans-serif;
  font-weight: bold;
  color: rgb(255, 255, 255);
  font-size: 16px;
  background-color: rgb(0, 103, 171);
  width: 200px;
  height: 40px;
  border: 0;
  border-radius: 6px !important;
  cursor: pointer;
}
.alert {
  padding: 0.75rem 1.25rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
  border-radius: 0.25rem;
}
.alert ul {
padding: 0px 20px;
}
.alert-danger {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
}
.alert-success {
  color: #155724;
  background-color: #d4edda;
  border-color: #c3e6cb;
}
* {box-sizing: border-box;}
        /* CSS property for header section */
  .header {
            background-color: green;
            padding: 15px;
            text-align: center;
  }

</style>
</head>
<body>


<div class="header">
        <h2 style="color:white;font-size:200%">
            Upload student data
        </h2>
    </div>
<div class="form">
<p style="background: orange;"><a href="scoordinator.php">Dashboard</a> 
| <a href="upload_stu_data.php">Add Data</a> 
</p>
</div>


<div style="width:700px; margin:50 auto;">

<?php
if(!empty($error)){
    echo "<div class='alert alert-danger'><ul>";
    echo $error;
    echo "</ul></div>";
    }
if(!empty($success)){
      echo "<div class='alert alert-success'><h2>".$success."</h2><ul>";
    echo $success_data;
    echo "</ul></div>";
    }
?>

<form method="post" action="" enctype="multipart/form-data">

<input type="file" name="upload_csv" class="form-control"/>
<br /><br />
<input type="submit" value="Upload Data" class="btn btn-info"/>
</form>

</div>
</body>
</html>




!-- Edit Student Form -->
            <?php if ($edit_student): ?>
                <form id="editStudentForm" method="POST" action="">
                    <h3>Edit Student Level</h3>
                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($edit_student['id']); ?>">
                    <label for="level">Select New Level:</label>
                    <select name="level" required>
                        <option value="">Select Level</option>
                        <option value="100" <?php if ($edit_student['level'] == 100) echo 'selected'; ?>>100</option>
                        <option value="200" <?php if ($edit_student['level'] == 200) echo 'selected'; ?>>200</option>
                        <option value="300" <?php if ($edit_student['level'] == 300) echo 'selected'; ?>>300</option>
                        <option value="400" <?php if ($edit_student['level'] == 400) echo 'selected'; ?>>400</option>
                        <option value="500" <?php if ($edit_student['level'] == 500) echo 'selected'; ?>>500</option>
                    </select>
                    <button type="submit" name="update_student">Update Level</button>
                </form>
            <?php endif; ?>

            //for table
            <a href="manage_students.php?edit_student=<?php echo $student['id']; ?>">Edit</a>

            // Handle Update Student php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_student'])) {
    $update_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $new_level = mysqli_real_escape_string($conn, $_POST['level']);
    
    // Update the student's level
    $stmt = $conn->prepare("UPDATE student SET level = ? WHERE id = ? AND department_id = ?");
    $stmt->bind_param("iis", $new_level, $update_id, $department_id);
    
    if ($stmt->execute()) {
        $message = "Student level updated successfully!";
    } else {
        $message = "Error updating student: " . $stmt->error;
    }
    
    $stmt->close();
    
    // Refresh the students list after update
    header("Location: manage_students.php"); // Replace with your actual file name
    exit();
}