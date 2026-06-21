<?php
include 'db_connect.php';
session_start();

// Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = "";

// 1. Fetch current user data
$user_sql = "SELECT username, email, role, created_at FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql)->fetch_assoc();

// 2. Handle Password Update
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $pass_sql = "SELECT password FROM users WHERE id = $user_id";
    $pass_result = $conn->query($pass_sql)->fetch_assoc();

    if (password_verify($current_password, $pass_result['password'])) {
        if ($new_password === $confirm_password) {
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

// 3. Dynamic UI Configuration Based on Role
$theme_color = "#2563eb"; // Default to Student Blue
$sidebar_title = "SECMS";
$sidebar_html = "";

if ($role === 'admin') {
    $theme_color = "#4f46e5"; // Admin Indigo
    $sidebar_title = "SECMS Admin";
    $sidebar_html = '
        <a href="admin_dashboard.php">Command Center</a>
        <a href="manage_academic_config.php">Academic Config</a>
        <a href="admin_assign_student.php">Assign Student Profiles</a>
        <a href="admin_profile.php">Profile Sheet</a>
        <a href="profile.php" class="active">Account Settings</a>
    ';
} elseif ($role === 'faculty') {
    $theme_color = "#059669"; // Faculty Emerald
    $sidebar_title = "SECMS Faculty";
    $sidebar_html = '
        <a href="faculty_dashboard.php">Overview Dashboard</a>
        <a href="faculty_marks.php">Manage Grades/Marks</a>
        <a href="faculty_curriculum.php">Course Curriculum Directory</a>
        <a href="faculty_attendance.php">Track Daily Attendance</a>
        <a href="faculty_profile.php">Profile Sheet</a>
        <a href="profile.php" class="active">Account Settings</a>
    ';
} else {
    // Student
    $theme_color = "#2563eb"; // Student Blue
    $sidebar_title = "SECMS Student";
    $sidebar_html = '
        <a href="student_dashboard.php">My Overview Portal</a>
        <a href="student_profile.php">My Profile Sheet</a>
        <a href="student_classmates.php">Classmates Directory</a>
        <a href="student_curriculum.php">Curriculum Roadmap</a>
        <a href="profile.php" class="active">Account Settings</a>
    ';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings - SECMS</title>
    <style>
        :root {
            --primary: #0f172a;
            --accent: <?php echo $theme_color; ?>; /* Dynamic based on role */
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-light: #64748b;
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: var(--accent); font-size: 20px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        .sidebar a.active { background: var(--accent); color: white; }
        
        /* Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 40px; box-sizing: border-box; max-width: 800px; }
        h1 { margin: 0 0 25px 0; font-size: 28px; }
        
        /* Card Layout */
        .card { background: var(--card); border: 1px solid var(--border); padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .card h2 { margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 15px; font-size: 18px; margin-bottom: 25px; }
        
        /* Info Grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-item { background: #f1f5f9; padding: 15px; border-radius: 8px; }
        .info-label { font-size: 12px; font-weight: 600; color: var(--text-light); text-transform: uppercase; margin-bottom: 4px; }
        .info-value { font-size: 15px; font-weight: 700; color: var(--text); }
        
        /* Forms */
        label { display: block; font-weight: 600; font-size: 13px; color: var(--text-light); margin-bottom: 6px; margin-top: 15px; }
        input { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; font-family: inherit; }
        input:focus { outline: 2px solid var(--accent); border-color: transparent; }
        
        button { background: var(--accent); color: white; border: none; font-weight: 600; cursor: pointer; padding: 12px 24px; border-radius: 6px; font-size: 14px; margin-top: 25px; transition: 0.2s; }
        button:hover { opacity: 0.9; }
        
        /* Alerts */
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #34d399; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #f87171; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><?php echo $sidebar_title; ?></h3>
    <?php echo $sidebar_html; ?>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out System</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: #64748b;">System Context: <span>Account Settings</span></div>
        <div style="font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <div style="width: 30px; height: 30px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <?php echo strtoupper(substr($user_result['username'], 0, 1)); ?>
            </div>
            <?php echo htmlspecialchars($user_result['username']); ?>
        </div>
    </header>

    <main class="main-content">
        <h1>Account & Security Settings</h1>
        
        <?php echo $message; ?>

        <div class="card">
            <h2>Account Details Overview</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Registered Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_result['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><code><?php echo htmlspecialchars($user_result['email']); ?></code></div>
                </div>
                <div class="info-item">
                    <div class="info-label">System Role Level</div>
                    <div class="info-value" style="text-transform: capitalize; color: var(--accent);"><?php echo htmlspecialchars($user_result['role']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Account Created On</div>
                    <div class="info-value"><?php echo date('F d, Y', strtotime($user_result['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Security: Change Password</h2>
            <form action="profile.php" method="POST">
                <label for="current_password">Verify Current Password</label>
                <input type="password" name="current_password" id="current_password" placeholder="Enter your current password" required>
                
                <label for="new_password">Create New Password</label>
                <input type="password" name="new_password" id="new_password" placeholder="Must be at least 8 characters" required>
                
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Type your new password again" required>
                
                <button type="submit" name="update_password">Update Security Credentials</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>