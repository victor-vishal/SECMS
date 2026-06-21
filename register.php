<?php
include 'db_connect.php';

$message = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $course_code = "NULL";
    $batch_id = "NULL";

    if ($role === 'student') {
        $course_code = !empty($_POST['course_code']) ? "'" . mysqli_real_escape_string($conn, $_POST['course_code']) . "'" : "NULL";
        $batch_id = !empty($_POST['batch_id']) ? intval($_POST['batch_id']) : "NULL";
    }

    $check_email = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check_email->num_rows > 0) {
        $message = "<div class='error'>This email is already registered!</div>";
    } else {
        $sql = "INSERT INTO users (username, email, password, role, status, course_code, batch_id) 
                VALUES ('$name', '$email', '$password', '$role', 'pending', $course_code, $batch_id)";
        
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='success'>Registration successful! Awaiting Admin approval.</div>";
        } else {
            $message = "<div class='error'>Registration failed: " . $conn->error . "</div>";
        }
    }
}

$live_courses = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
$live_batches = $conn->query("SELECT * FROM batches ORDER BY batch_name DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS - Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .register-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 320px; margin: 20px 0; }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        input, select, button { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #007BFF; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .error { color: red; font-size: 14px; text-align: center; }
        .success { color: green; font-size: 14px; text-align: center; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .login-link a { color: #007BFF; text-decoration: none; }
    </style>
</head>
<body>

<div class="register-container">
    <h2>SECMS Registration</h2>
    <?php echo $message; ?>
    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <label for="role">Register As:</label>
        <select name="role" id="role_selector" onchange="toggleStudentFields()" required>
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty Member</option>
            <option value="admin">Administrator</option>
        </select>

        <div id="student_academic_fields" style="display: none;">
            <label for="course_code">Assigned Academic Course:</label>
            <select name="course_code" id="course_code">
                <option value="">-- Choose Course Stream --</option>
                <?php while($c = $live_courses->fetch_assoc()): ?>
                    <option value="<?php echo $c['course_code']; ?>">
                        [<?php echo $c['course_code']; ?>] <?php echo $c['course_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="batch_id">Academic Graduation Batch Frame:</label>
            <select name="batch_id" id="batch_id">
                <option value="">-- Choose Graduation Batch --</option>
                <?php while($b = $live_batches->fetch_assoc()): ?>
                    <option value="<?php echo $b['id']; ?>">
                        Batch <?php echo $b['batch_name']; ?> (Current Sem: <?php echo $b['current_semester']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div> <button type="submit" name="register">Submit Request</button>
    </form>
    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
function toggleStudentFields() {
    var roleSelector = document.getElementById('role_selector');
    var studentFieldsDiv = document.getElementById('student_academic_fields');
    
    var courseInput = document.getElementById('course_code');
    var batchInput = document.getElementById('batch_id');

    if (roleSelector.value === 'student') {
        studentFieldsDiv.style.display = 'block';
        courseInput.required = true;
        batchInput.required = true;
    } else {
        studentFieldsDiv.style.display = 'none';
        courseInput.required = false;
        batchInput.required = false;
        courseInput.value = "";
        batchInput.value = "";
    }
}
</script>

</body>
</html>