<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];
$query = $conn->query("SELECT * FROM users WHERE id = $faculty_id");
$user_data = $query->fetch_assoc();

$faculty_name = $user_data['username'] ?? 'Faculty Member';
$email = $user_data['email'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Account Profile</title>
    <style>
        :root { --primary: #0f172a; --accent: #059669; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #1e293b; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #34d399; font-size: 20px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; }
        .sidebar a:hover { background: #1e293b; color: white; }
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        .profile-menu { position: relative; display: inline-block; cursor: pointer; }
        .profile-trigger { display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; font-weight: 600; font-size: 14px; border: 1px solid var(--border); }
        .profile-avatar { width: 24px; height: 24px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .dropdown-content { display: none; position: absolute; right: 0; top: 45px; background: white; min-width: 160px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid var(--border); z-index: 10; overflow: hidden; }
        .dropdown-content a { color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; }
        .dropdown-content a:hover { background: #f1f5f9; }
        .profile-menu:hover .dropdown-content { display: block; }
        .main-content { padding: 40px; box-sizing: border-box; }
        .profile-card { background: var(--card); border: 1px solid var(--border); padding: 35px; border-radius: 12px; max-width: 600px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .meta-row { display: flex; padding: 15px 0; border-bottom: 1px solid #f1f5f9; }
        .meta-row:last-child { border-bottom: none; }
        .meta-label { width: 180px; font-weight: 600; color: #64748b; font-size: 14px; }
        .meta-val { font-weight: 700; color: #0f172a; font-size: 15px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php">Overview Dashboard</a>
    <a href="faculty_marks.php">Manage Grades/Marks</a>
    <a href="faculty_curriculum.php">Course Curriculum Directory</a>
    <a href="faculty_attendance.php">Track Daily Attendance</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: #64748b;">System Context: <span>Profile Settings</span></div>
        <div class="profile-menu">
            <div class="profile-trigger">
                <div class="profile-avatar"><?php echo strtoupper(substr($faculty_name, 0, 1)); ?></div>
                <?php echo htmlspecialchars($faculty_name); ?> ▼
            </div>
            <div class="dropdown-content">
                <a href="faculty_profile.php">My Account Profile</a>
                <a href="logout.php" style="color: #ef4444; border-top: 1px solid #f1f5f9;">Sign Out</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <h1 style="margin: 0 0 25px 0; font-size: 28px;">My Account Profile</h1>
        
        <div class="profile-card">
            <div class="meta-row">
                <div class="meta-label">Full Name Descriptor</div>
                <div class="meta-val"><?php echo htmlspecialchars($faculty_name); ?></div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Email Address</div>
                <div class="meta-val"><code><?php echo htmlspecialchars($email); ?></code></div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Administrative Role</div>
                <div class="meta-val" style="color: var(--accent);">Faculty Educator</div>
            </div>
        </div>
    </main>
</div>

</body>
</html>