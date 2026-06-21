<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['username'] ?? 'Student';

// Fetch assigned course stream
$profile_query = $conn->query("SELECT course_code FROM users WHERE id = $student_id");
$profile = $profile_query->fetch_assoc();
$course = $profile['course_code'] ?? '';

// Query subjects belonging to the stream
$subjects_res = null;
if (!empty($course)) {
    $subjects_res = $conn->query("SELECT * FROM subjects WHERE course_code = '$course' ORDER BY semester ASC, subject_code ASC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Curriculum Roadmap - SECMS</title>
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
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background: #f8fafc; color: var(--text-light); font-weight: 600; font-size: 14px; }
        
        .sem-badge { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 13px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Student</h3>
    <a href="student_dashboard.php">My Overview Portal</a>
    <a href="student_profile.php">My Profile Sheet</a>
    <a href="student_classmates.php">Classmates Directory</a>
    <a href="student_curriculum.php" class="active">Curriculum Roadmap</a>
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
        <h1>Academic Curriculum Roadmap</h1>
        <div class="subtitle">Review the master blueprint of subjects assigned to your degree track.</div>

        <div class="card" style="border-top: 4px solid var(--accent);">
            <h2 style="margin-top: 0; font-size: 18px; margin-bottom: 20px;">Stream Blueprint: <?php echo htmlspecialchars($course ?: 'Unassigned'); ?></h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Academic Term Phase</th>
                        <th>Subject Identity Code</th>
                        <th>Subject Description Nomenclature</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($subjects_res && $subjects_res->num_rows > 0): while($s = $subjects_res->fetch_assoc()): ?>
                    <tr>
                        <td><span class="sem-badge">Semester <?php echo $s['semester']; ?></span></td>
                        <td><code><?php echo htmlspecialchars($s['subject_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($s['subject_name']); ?></strong></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" style="text-align:center; padding:30px; color:var(--text-light);">No course mapping data generated for your account yet. Please contact the Admin.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>