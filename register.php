<?php
include 'db_connect.php';
session_start();

// If already logged in, route them away
if (isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        $message = "<div class='error-msg'>Passwords do not match!</div>";
    } else {
        // Check if username or email already exists
        $check = $conn->query("SELECT id FROM users WHERE email = '$email' OR username = '$username'");
        if ($check->num_rows > 0) {
            $message = "<div class='error-msg'>This Username or Email is already registered.</div>";
        } else {
            // Hash password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user as 'pending'
            $sql = "INSERT INTO users (username, email, password, role, status) VALUES ('$username', '$email', '$hashed_password', '$role', 'pending')";
            
            if ($conn->query($sql) === TRUE) {
                $message = "<div class='success-msg'>Registration request submitted! Please wait for Admin approval.</div>";
            } else {
                $message = "<div class='error-msg'>System Error: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SECMS</title>
    <style>
        :root {
            --primary: #4f46e5; --primary-hover: #4338ca;
            --bg: #f1f5f9; --card: #ffffff; --border: #e2e8f0; 
            --text: #0f172a; --text-light: #64748b; 
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;}
        
        .auth-card { background: var(--card); padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; border: 1px solid var(--border); box-sizing: border-box; }
        
        .logo-area { text-align: center; margin-bottom: 25px; }
        .logo-area h1 { margin: 0; font-size: 28px; color: var(--primary); letter-spacing: -0.5px; }
        .logo-area p { margin: 5px 0 0 0; color: var(--text-light); font-size: 14px; }
        
        form { display: grid; grid-template-columns: 1fr; gap: 15px; }
        
        label { font-size: 13px; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: -5px;}
        input, select { width: 100%; padding: 12px 14px; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; font-size: 14px; transition: 0.2s; background: #f8fafc; font-family: inherit;}
        input:focus, select:focus { outline: none; border-color: var(--primary); background: #ffffff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        
        button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; padding: 14px; border-radius: 8px; font-size: 15px; margin-top: 10px; transition: 0.2s; }
        button:hover { background: var(--primary-hover); transform: translateY(-1px); }
        
        .error-msg { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; border: 1px solid #f87171; text-align: center; }
        .success-msg { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; border: 1px solid #34d399; text-align: center; }
        
        .footer-link { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-light); }
        .footer-link a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="logo-area">
        <h1>Join SECMS</h1>
        <p>Request access to the campus network</p>
    </div>

    <?php echo $message; ?>

    <form action="register.php" method="POST">
        <div>
            <label for="username">System Username (Used for Login)</label>
            <input type="text" name="username" id="username" placeholder="e.g. JohnDoe" required autofocus>
        </div>

        <div>
            <label for="email">Campus Email Address</label>
            <input type="email" name="email" id="email" placeholder="name@secms.edu" required>
        </div>

        <div>
            <label for="role">Requested Access Role</label>
            <select name="role" id="role" required>
                <option value="student">Student Enrollee</option>
                <option value="faculty">Faculty / Staff Member</option>
                <option value="admin">System Administrator</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
            </div>
            <div>
                <label for="confirm_password">Confirm</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" required>
            </div>
        </div>
        
        <button type="submit" name="register">Submit Registration Request</button>
    </form>
    
    <div class="footer-link">
        Already have an approved account? <a href="login.php">Sign in here</a>
    </div>
</div>

</body>
</html>