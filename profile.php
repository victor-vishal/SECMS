<?php
include 'db_connect.php';
session_start();

// Security Check: Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Fetch current user data
$user_sql = "SELECT username, email, role, created_at FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql)->fetch_assoc();

// 2. Handle Password Update Form Submission
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password from DB
    $pass_sql = "SELECT password FROM users WHERE id = $user_id";
    $pass_result = $conn->query($pass_sql)->fetch_assoc();

    if (password_verify($current_password, $pass_result['password'])) {
        if ($new_password === $confirm_password) {
            // Hash the new password safely
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_sql = "UPDATE users SET password = '$new_hashed_password' WHERE id = $user_id";
            if ($conn->query($update_sql) === TRUE) {
                $message = "<div class='success'>Password updated successfully!</div>";
            } else {
                $message = "<div class='error'>Error updating password: " . $conn->error . "</div>";
            }
        } else {
            $message = "<div class='error'>New passwords do not match!</div>";
        }
    } else {
        $message = "<div class='error'>Current password is incorrect!</div>";
    }
}

// Set up back button link dynamically based on who is logged in
$back_url = "student_dashboard.php";
if ($_SESSION['role'] === 'admin') {
    $back_url = "admin_dashboard.php";
} elseif ($_SESSION['role'] === 'faculty') {
    $back_url = "faculty_dashboard.php";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS - My Profile</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .profile-container { max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { color: #333; margin-top: 0; border-bottom: 2px solid #6c757d; padding-bottom: 10px; }
        .info-group { margin-bottom: 15px; font-size: 16px; }
        .info-group strong { color: #555; }
        .btn-back { display: inline-block; background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-bottom: 20px; }
        .btn-back:hover { background: #5a6268; }
        label { display: block; margin-top: 15px; font-weight: bold; font-size: 14px; color: #495057; }
        input, button { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #007BFF; color: white; border: none; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 20px; }
        button:hover { background: #0056b3; }
        .success { color: green; font-weight: bold; margin-bottom: 15px; font-size: 14px; }
        .error { color: red; font-weight: bold; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>

<div class="profile-container">
    <a href="<?php echo $back_url; ?>" class="btn-back">← Back to Dashboard</a>
    
    <h2>My Profile Details</h2>
    <?php echo $message; ?>

    <div class="info-group"><strong>Username:</strong> <?php echo htmlspecialchars($user_result['username']); ?></div>
    <div class="info-group"><strong>Email Address:</strong> <?php echo htmlspecialchars($user_result['email']); ?></div>
    <div class="info-group"><strong>Account Role:</strong> <?php echo ucfirst($user_result['role']); ?></div>
    <div class="info-group"><strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($user_result['created_at'])); ?></div>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

    <h2>Change Password</h2>
    <form action="profile.php" method="POST">
        <label for="current_password">Current Password:</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit" name="update_password">Update Password</button>
    </form>
</div>

</body>
</html>