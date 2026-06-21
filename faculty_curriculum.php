<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

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
    <title>Faculty Terminal - Curriculum Map</title>
    <style>
        :root { --primary: #4f46e5; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; color: #38bdf8; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background: #f1f5f9; }
        select { padding: 10px; width: 100%; max-width: 300px; border-radius: 6px; border: 1px solid var(--border); }
    </style>
</head>
<body>
<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php">Overview Dashboard</a>
    <a href="faculty_marks.php">Manage Grades/Marks</a>
    <a href="faculty_curriculum.php" class="active">Course Curriculum Directory</a>
    <a href="faculty_attendance.php">Track Daily Attendance</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <h1>Course Curriculum Directory Matrix</h1>
    <div class="card">
        <form action="faculty_curriculum.php" method="GET">
            <label style="display:block; margin-bottom:8px; font-weight:600;">Select Stream to Map:</label>
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
        <div class="card">
            <h2>Curriculum Blueprint Structure</h2>
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
                            <td><strong>Semester <?php echo $s['semester']; ?></strong></td>
                            <td><code><?php echo htmlspecialchars($s['subject_code']); ?></code></td>
                            <td><?php echo htmlspecialchars($s['subject_name']); ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align:center; padding:20px; color:#94a3b8;">No subjects mapped to this specific stream yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>