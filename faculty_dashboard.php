<?php
include 'db_connect.php';
session_start();

// Security check: Only faculty allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

// Fetch some quick dashboard stats for metrics cards
$total_students = $conn->query("SELECT COUNT(id) as count FROM users WHERE role='student' AND status='approved'")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(course_code) as count FROM courses")->fetch_assoc()['count'];
$total_subjects = $conn->query("SELECT COUNT(id) as count FROM subjects")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Workspace - Home Overview</title>
    <style>
        :root { --primary: #4f46e5; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; color: #38bdf8; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        
        .welcome-hero { background: linear-gradient(135deg, #1e293b, #4f46e5); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .welcome-hero h2 { margin: 0 0 10px 0; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card); padding: 20px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .stat-card h3 { margin: 0; color: #64748b; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card p { margin: 10px 0 0 0; font-size: 28px; font-weight: 700; color: #1e293b; }
        
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php" class="active">Overview Dashboard</a>
    <a href="faculty_marks.php">Manage Grades/Marks</a>
    <a href="faculty_curriculum.php">Course Curriculum Directory</a>
    <a href="faculty_attendance.php">Track Daily Attendance</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <!-- Welcome Header Banner -->
    <div class="welcome-hero">
        <h2>Welcome to the Faculty Control Terminal</h2>
        <p style="margin: 0; opacity: 0.9;">Manage student academic evaluations, roll call attendance logs, and track curriculum frameworks from a single workspace.</p>
    </div>

    <!-- Quick Analytics Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Approved Enrolled Students</h3>
            <p><?php echo $total_students; ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Course Streams</h3>
            <p><?php echo $total_courses; ?></p>
        </div>
        <div class="stat-card">
            <h3>Registered Subjects</h3>
            <p><?php echo $total_subjects; ?></p>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="card">
        <h3 style="margin-top: 0; color: #1e293b;">Quick Launch Workspace Guide</h3>
        <p style="color: #475569; line-height: 1.6; margin-bottom: 0;">
            Use the structural navigation options inside the left sidebar panel to manage your daily academic workflows. 
            You can dynamically input grading data through the <strong>Manage Grades</strong> portal or record attendance ledgers using real-time relational class tracking metrics.
        </p>
    </div>
</div>

</body>
</html>