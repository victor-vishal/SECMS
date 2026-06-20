<?php
include 'db_connect.php';

$message = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Secure password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check_user = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $result = $conn->query($check_user);

    if ($result->num_rows > 0) {
        $message = "<div class='error'>Username or Email already exists!</div>";
    } else {
        // Status defaults to 'pending' as defined in the database schema
        $sql = "INSERT INTO users (username, password, email, role, status) VALUES ('$username', '$hashed_password', '$email', '$role', 'pending')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='success'>Registration successful! Please wait for Admin approval.</div>";
        } else {
            $message = "<div class='error'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS - Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 320px; }
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
        <select name="role" id="role" required>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="admin">Admin</option>
        </select>
        
        <button type="submit" name="register">Submit Request</button>
    </form>
    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>