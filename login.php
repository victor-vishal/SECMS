<?php
include 'db_connect.php';
session_start();

// If already logged in, redirect to correct dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin_dashboard.php");
    elseif ($_SESSION['role'] === 'faculty') header("Location: faculty_dashboard.php");
    else header("Location: student_dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    // Search the database by Username instead of Email
    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if account is approved by admin
        if ($user['status'] === 'pending') {
            $error = "Your account is currently pending Admin approval.";
        } else {
            // Verify Password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Route to correct dashboard
                if ($user['role'] === 'admin') header("Location: admin_dashboard.php");
                elseif ($user['role'] === 'faculty') header("Location: faculty_dashboard.php");
                else header("Location: student_dashboard.php");
                exit();
            } else {
                $error = "Incorrect password provided.";
            }
        }
    } else {
        $error = "No account found with this username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SECMS</title>
    <style>
        :root {
            --primary: #4f46e5; --primary-hover: #4338ca;
            --bg: #f1f5f9; --card: #ffffff; --border: #e2e8f0; 
            --text: #0f172a; --text-light: #64748b; --danger: #ef4444;
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        
        .auth-card { background: var(--card); padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 400px; border: 1px solid var(--border); box-sizing: border-box; }
        
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area h1 { margin: 0; font-size: 32px; color: var(--primary); letter-spacing: -1px; }
        .logo-area p { margin: 5px 0 0 0; color: var(--text-light); font-size: 15px; }
        
        form { display: flex; flex-direction: column; gap: 15px; }
        
        label { font-size: 13px; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; }
        input { width: 100%; padding: 14px; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; font-size: 15px; transition: 0.2s; background: #f8fafc; }
        input:focus { outline: none; border-color: var(--primary); background: #ffffff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        
        button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; padding: 14px; border-radius: 8px; font-size: 16px; margin-top: 10px; transition: 0.2s; }
        button:hover { background: var(--primary-hover); transform: translateY(-1px); }
        
        .error-msg { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; border: 1px solid #f87171; text-align: center; }
        
        .footer-link { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-light); }
        .footer-link a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="logo-area">
        <h1>SECMS</h1>
        <p>Sign in to your campus workspace</p>
    </div>

    <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div>
            <label for="username">System Username</label>
            <input type="text" name="username" id="username" placeholder="Enter your username" required autofocus>
        </div>
        
        <div>
            <label for="password">Security Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" name="login">Secure Sign In</button>
    </form>
    
    <div class="footer-link">
        New to the system? <a href="register.php">Request an account</a>
    </div>
</div>

</body>
</html>