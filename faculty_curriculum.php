<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$faculty_name = $_SESSION['username'] ?? 'Faculty Member';
$courses = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
$selected_course = isset($_GET['course_code']) ? mysqli_real_escape_string($conn, $_GET['course_code']) : "";
$subjects_res = null;

if (!empty($selected_course)) {
    $subjects_res = $conn->query("SELECT * FROM subjects WHERE course_code = '$selected_course' ORDER BY semester, subject_code ASC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Curriculum Directory - Faculty SECMS</title>
    <style>
        :root { --primary: #0f172a; --accent: #059669; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #1e293b; --text-light: #64748b; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #34d399; font-size: 20px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        .sidebar a.active { background: var(--accent); }
        
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 40px; box-sizing: border-box; overflow-y: auto; }
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        .subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 15px; }
        
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 30px; }
        
        label { font-size: 13px; font-weight: 600; color: var(--text-light); display: block; margin-bottom: 6px; }
        select { width: 100%; max-width: 400px; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background: #f8fafc; color: var(--text-light); font-weight: 600; }
        
        .sem-badge { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 13px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php">Overview Dashboard</a>
    <a href="faculty_marks.php">Manage Grades/Marks</a>
    <a href="faculty_curriculum.php" class="active">Course Curriculum Directory</a>
    <a href="faculty_attendance.php">Track Daily Attendance</a>
    <a href="faculty_profile.php">Profile Sheet</a>
    <a href="profile.php">Account Settings</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: #64748b;">Workspace Context: <strong>Faculty Terminal</strong></div>
        <div class="profile-menu" style="position: relative; display: inline-block;">
            <div class="profile-trigger" style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 14px; border: 1px solid var(--border);" onclick="var d = document.getElementById('fac-drop'); d.style.display = d.style.display === 'block' ? 'none' : 'block';">
                <div style="width: 28px; height: 28px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                    <?php echo strtoupper(substr($faculty_name, 0, 1)); ?>
                </div>
                <?php echo htmlspecialchars($faculty_name); ?> ▾
            </div>
            <div id="fac-drop" style="display: none; position: absolute; right: 0; top: 48px; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid var(--border); z-index: 50; overflow: hidden;">
                <a href="faculty_profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-bottom: 1px solid #f1f5f9;">👤 Profile Sheet</a>
                <a href="profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px;">⚙️ Account Settings</a>
                <a href="logout.php" style="color: #ef4444; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-top: 1px solid #f1f5f9;">🚪 Sign Out</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <h1>Course Curriculum Directory</h1>
        <div class="subtitle">Review the active subject blueprints assigned to degree streams.</div>

        <div class="card">
            <form action="faculty_curriculum.php" method="GET">
                <label>Select Stream to Map:</label>
                <select name="course_code" onchange="this.form.submit()">
                    <option value="">-- Choose Stream --</option>
                    <?php while($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $c['course_code']; ?>" <?php if($selected_course == $c['course_code']) echo 'selected'; ?>>
                            [<?php echo $c['course_code']; ?>] <?php echo $c['course_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <?php if ($subjects_res): ?>
        <div class="card" style="border-top: 4px solid var(--accent);">
            <h2 style="margin-top: 0; font-size: 18px; margin-bottom: 20px;">Curriculum Blueprint Structure</h2>
            <table>
                <thead>
                    <tr>
                        <th>Academic Term</th>
                        <th>Subject Code</th>
                        <th>Subject Descriptive Nomenclature</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($subjects_res->num_rows > 0): while($s = $subjects_res->fetch_assoc()): ?>
                    <tr>
                        <td><span class="sem-badge">Semester <?php echo $s['semester']; ?></span></td>
                        <td><code><?php echo htmlspecialchars($s['subject_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($s['subject_name']); ?></strong></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" style="text-align:center; padding:30px; color:#94a3b8;">No subjects mapped to this specific stream yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>