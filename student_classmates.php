<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$profile = $conn->query("SELECT course_code, batch_id FROM users WHERE id = $student_id")->fetch_assoc();

$course = $profile['course_code'];
$batch = $profile['batch_id'];

// Fetch peers in the exact same track
$peers = $conn->query("SELECT username, email FROM users WHERE role='student' AND status='approved' AND course_code='$course' AND batch_id=$batch AND id != $student_id ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Terminal - Classmates</title>
    <style>
        :root { --primary: #3b82f6; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; color: #60a5fa; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); }
        .peer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
        .peer-card { background: #f1f5f9; padding: 15px; border-radius: 8px; border: 1px solid var(--border); text-align: center; }
        .peer-card h4 { margin: 0 0 5px 0; color: #1e293b; }
        .peer-card p { margin: 0; font-size: 13px; color: #64748b; font-family: monospace; }
    </style>
</head>
<body>
<div class="sidebar">
    <h3>SECMS Student</h3>
    <a href="student_dashboard.php">My Overview Portal</a>
    <a href="student_classmates.php" class="active">My Classmates Directory</a>
    <a href="student_curriculum.php">Curriculum Roadmap</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <h1>Classmates Directory</h1>
    <div class="card">
        <h3>My Cohort Peers</h3>
        <div class="peer-grid">
            <?php if($peers->num_rows > 0): while($p = $peers->fetch_assoc()): ?>
                <div class="peer-card">
                    <h4><?php echo htmlspecialchars($p['username']); ?></h4>
                    <p><?php echo htmlspecialchars($p['email']); ?></p>
                </div>
            <?php endwhile; else: ?>
                <p style="color: #94a3b8;">You are currently the solitary student assigned to this track.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>