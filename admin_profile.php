<?php
include 'db_connect.php';
session_start();

// Security Gate Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$query = $conn->query("SELECT * FROM users WHERE id = $admin_id");
$user_data = $query->fetch_assoc();

$admin_user = $user_data['username'] ?? 'Admin';
$email = $user_data['email'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - SECMS</title>
    <style>
        :root { 
            --primary: #0f172a; 
            --accent: #4f46e5;
            --accent-hover: #4338ca;
            --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; --text-light: #64748b; 
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #818cf8; font-size: 20px; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover { background: #1e293b; color: white; }
        .sidebar a.active { background: var(--accent); color: white; }
        
        /* Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 40px; box-sizing: border-box; }
        .profile-card { background: var(--card); border: 1px solid var(--border); padding: 35px; border-radius: 12px; max-width: 600px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .meta-row { display: flex; padding: 15px 0; border-bottom: 1px solid #f1f5f9; align-items: center; }
        .meta-row:last-child { border-bottom: none; }
        .meta-label { width: 180px; font-weight: 600; color: var(--text-light); font-size: 14px; }
        .meta-val { font-weight: 700; color: var(--text); font-size: 15px; }
        
        .btn-settings { display: inline-block; background: #f1f5f9; color: var(--text); border: 1px solid var(--border); padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; margin-top: 25px; transition: 0.2s; }
        .btn-settings:hover { background: var(--accent); color: white; border-color: var(--accent); }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Admin</h3>
    <a href="admin_dashboard.php">Command Center</a>
    <a href="manage_academic_config.php">Academic Config</a>
    <a href="admin_assign_student.php">Assign Student Profiles</a>
    <a href="admin_profile.php" class="active">Profile Sheet</a>
    <a href="profile.php">Account Settings</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out System</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: var(--text-light);">System Context: <strong>Profile Sheet</strong></div>
        
        <div class="profile-menu" style="position: relative; display: inline-block;">
            <div class="profile-trigger" style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 14px; border: 1px solid var(--border);" onclick="var d = document.getElementById('admin-drop'); d.style.display = d.style.display === 'block' ? 'none' : 'block';">
                <div style="width: 28px; height: 28px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                    <?php echo strtoupper(substr($admin_user, 0, 1)); ?>
                </div>
                <?php echo htmlspecialchars($admin_user); ?> ▾
            </div>
            
            <div id="admin-drop" style="display: none; position: absolute; right: 0; top: 48px; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid var(--border); z-index: 50; overflow: hidden;">
                <a href="admin_profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-bottom: 1px solid #f1f5f9;">👤 Profile Sheet</a>
                <a href="profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px;">⚙️ Account Settings</a>
                <a href="logout.php" style="color: #ef4444; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-top: 1px solid #f1f5f9;">🚪 Sign Out</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 600px; margin-bottom: 25px;">
            <h1 style="margin: 0; font-size: 28px;">My Profile Sheet</h1>
            <a href="profile.php" class="btn-settings">⚙️ Account Settings</a>
        </div>
        
        <div class="profile-card" style="border-top: 4px solid var(--accent);">
            <div class="meta-row">
                <div class="meta-label">System Admin Name</div>
                <div class="meta-val"><?php echo htmlspecialchars($admin_user); ?></div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Registered Email</div>
                <div class="meta-val"><code><?php echo htmlspecialchars($email); ?></code></div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Access Level Clearance</div>
                <div class="meta-val" style="color: var(--accent); text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Super Administrator</div>
            </div>
        </div>
    </main>
</div>

</body>
</html>