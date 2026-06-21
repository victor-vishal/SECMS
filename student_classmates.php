<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['username'] ?? 'Student';

// Fetch the student's current assigned track
$profile = $conn->query("SELECT course_code, batch_id FROM users WHERE id = $student_id")->fetch_assoc();
$course = $profile['course_code'];
$batch = $profile['batch_id'];

// Fetch peers in the exact same track
$peers = null;
if (!empty($course) && !empty($batch)) {
    $peers = $conn->query("SELECT username, email FROM users WHERE role='student' AND status='approved' AND course_code='$course' AND batch_id=$batch AND id != $student_id ORDER BY username ASC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Classmates Directory - SECMS</title>
    <style>
        :root { --primary: #0f172a; --accent: #2563eb; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #1e293b; --text-light: #64748b; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #60a5fa; font-size: 20px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        .sidebar a.active { background: var(--accent); }
        
        /* Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 40px; box-sizing: border-box; overflow-y: auto; }
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        .subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 15px; }
        
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        
        /* Peer Grid */
        .peer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; margin-top: 15px; }
        .peer-card { background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid var(--border); text-align: center; transition: 0.2s; }
        .peer-card:hover { border-color: #93c5fd; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transform: translateY(-2px); }
        
        .peer-avatar { width: 50px; height: 50px; background: #e0f2fe; color: #0369a1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; margin: 0 auto 15px auto; }
        .peer-card h4 { margin: 0 0 5px 0; color: #1e293b; font-size: 16px; }
        .peer-card p { margin: 0; font-size: 13px; color: var(--text-light); }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Student</h3>
    <a href="student_dashboard.php">My Overview Portal</a>
    <a href="student_profile.php">My Profile Sheet</a>
    <a href="student_classmates.php" class="active">Classmates Directory</a>
    <a href="student_curriculum.php">Curriculum Roadmap</a>
    <a href="profile.php">Account Settings</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: #64748b;">Workspace Context: <strong>Student Terminal</strong></div>
        
        <div class="profile-menu" style="position: relative; display: inline-block;">
            <div class="profile-trigger" style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 14px; border: 1px solid var(--border);" onclick="var d = document.getElementById('stu-drop'); d.style.display = d.style.display === 'block' ? 'none' : 'block';">
                <div style="width: 28px; height: 28px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                    <?php echo strtoupper(substr($student_name, 0, 1)); ?>
                </div>
                <?php echo htmlspecialchars($student_name); ?> ▾
            </div>
            
            <div id="stu-drop" style="display: none; position: absolute; right: 0; top: 48px; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid var(--border); z-index: 50; overflow: hidden;">
                <a href="student_profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-bottom: 1px solid #f1f5f9;">👤 Profile Sheet</a>
                <a href="profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px;">⚙️ Account Settings</a>
                <a href="logout.php" style="color: #ef4444; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-top: 1px solid #f1f5f9;">🚪 Sign Out</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <h1>Classmates Directory</h1>
        <div class="subtitle">Connect with peers enrolled in your specific stream and batch.</div>
        
        <div class="card" style="border-top: 4px solid var(--accent);">
            <h2 style="margin-top: 0; font-size: 18px; margin-bottom: 10px;">My Cohort Peers (<?php echo htmlspecialchars($course ?? 'Unassigned'); ?>)</h2>
            
            <div class="peer-grid">
                <?php if($peers && $peers->num_rows > 0): while($p = $peers->fetch_assoc()): ?>
                <div class="peer-card">
                    <div class="peer-avatar"><?php echo strtoupper(substr($p['username'], 0, 1)); ?></div>
                    <h4><?php echo htmlspecialchars($p['username']); ?></h4>
                    <p><?php echo htmlspecialchars($p['email']); ?></p>
                </div>
                <?php endwhile; else: ?>
                <div style="grid-column: 1 / -1; padding: 30px; text-align: center; color: var(--text-light); background: #f8fafc; border-radius: 8px;">
                    You are currently the only student assigned to this specific track or you have not been assigned to a track by the Admin yet.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>