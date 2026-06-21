<?php
include 'db_connect.php';
session_start();

// Security check: Only students allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Get logged-in student details along with academic info
$student_id = $_SESSION['user_id']; // Ensure your login system saves the user ID to this key
$student_query = "
    SELECT u.username, u.email, u.course_code, u.batch_id, b.batch_name, b.current_semester, c.course_name 
    FROM users u
    LEFT JOIN batches b ON u.batch_id = b.id
    LEFT JOIN courses c ON u.course_code = c.course_code
    WHERE u.id = $student_id
";
$student_res = $conn->query($student_query)->fetch_assoc();

$course_code = $student_res['course_code'];
$current_semester = $student_res['current_semester'];
$batch_id = $student_res['batch_id'];

// Fetch marks and active semester subjects dynamically
$report_card_res = null;
if (!empty($course_code) && !empty($current_semester)) {
    $report_card_query = "
        SELECT s.subject_code, s.subject_name, m.marks_obtained, m.total_marks 
        FROM subjects s
        LEFT JOIN marks m ON s.id = m.subject_id AND m.student_id = $student_id
        WHERE s.course_code = '$course_code' AND s.semester = $current_semester
        ORDER BY s.subject_name ASC
    ";
    $report_card_res = $conn->query($report_card_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Terminal - Dashboard</title>
    <style>
        :root { --primary: #3b82f6; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; color: #60a5fa; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        
        .profile-hero { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .profile-hero h2 { margin: 0 0 10px 0; }
        .meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 15px; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; }
        
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 14px 12px; border-bottom: 1px solid var(--border); }
        th { background: #f1f5f9; color: #475569; font-weight: 600; }
        .badge { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .status-pill { padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        .graded { background: #d1fae5; color: #065f46; }
        .pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Student</h3>
    <a href="student_dashboard.php" class="active">My Overview Portal</a>
    <a href="student_classmates.php">My Classmates Directory</a>
    <a href="student_curriculum.php">Curriculum Roadmap</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <div class="profile-hero">
        <h2>Welcome Back, <?php echo htmlspecialchars($student_res['username']); ?>!</h2>
        <p style="margin: 0; opacity: 0.9;">System ID Access Terminal</p>
        
        <div class="meta-grid">
            <div><strong>Stream:</strong> <?php echo !empty($student_res['course_name']) ? htmlspecialchars($student_res['course_name']) : 'Unassigned'; ?></div>
            <div><strong>Batch Track:</strong> <?php echo !empty($student_res['batch_name']) ? htmlspecialchars($student_res['batch_name']) : 'Unassigned'; ?></div>
            <div><strong>Academic Phase:</strong> <span class="badge">Semester <?php echo !empty($current_semester) ? $current_semester : '0'; ?></span></div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top:0; color:#1e293b; border-bottom: 1px solid var(--border); padding-bottom:10px;">Current Term Performance Metric</h2>
        <table>
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Name Description</th>
                    <th>Scored Marks</th>
                    <th>Evaluation Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($report_card_res && $report_card_res->num_rows > 0):
                    while($row = $report_card_res->fetch_assoc()):
                        $has_marks = ($row['marks_obtained'] !== null);
                ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($row['subject_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($row['subject_name']); ?></strong></td>
                        <td>
                            <?php echo $has_marks ? $row['marks_obtained'] . " / " . $row['total_marks'] : '--'; ?>
                        </td>
                        <td>
                            <?php if ($has_marks): ?>
                                <span class="status-pill graded">Marks Published</span>
                            <?php else: ?>
                                <span class="status-pill pending">Awaiting Assessment</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 30px; color: #94a3b8;">
                            No curriculum maps generated for your assigned academic profile metadata yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>