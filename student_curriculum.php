<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// 1. Fetch your assigned profile details to get your branch code
$profile_query = $conn->query("SELECT course_code FROM users WHERE id = $student_id");
$profile = $profile_query->fetch_assoc();

// Fallback to 'CD' or an empty string if not mapped yet
$course = isset($profile['course_code']) ? $profile['course_code'] : '';

// 2. Query ONLY the subjects that belong to your specific stream
$subjects_res = null;
if (!empty($course)) {
    $subjects_res = $conn->query("SELECT * FROM subjects WHERE course_code = '$course' ORDER BY semester ASC, subject_code ASC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Terminal - Curriculum Roadmap</title>
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; color: #60a5fa; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background: #f1f5f9; color: #475569; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Student</h3>
    <a href="student_dashboard.php">My Overview Portal</a>
    <a href="student_classmates.php">My Classmates Directory</a>
    <a href="student_curriculum.php" class="active">Curriculum Roadmap</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <h1>Academic Degree Curriculum Roadmap</h1>
    <div class="card">
        <h2>Complete 8-Semester Structural Map for Stream: <?php echo htmlspecialchars($course); ?></h2>
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
                        <td><strong>Semester <?php echo $s['semester']; ?></strong></td>
                        <td><code><?php echo htmlspecialchars($s['subject_code']); ?></code></td>
                        <td><?php echo htmlspecialchars($s['subject_name']); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="3" style="text-align:center; padding:20px; color:#94a3b8;">No course mapping data generated for this branch code yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>