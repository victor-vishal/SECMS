<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// 1. Fetch Profile Data
$query = $conn->query("SELECT u.*, b.batch_name, b.current_semester FROM users u LEFT JOIN batches b ON u.batch_id = b.id WHERE u.id = $student_id");
$user_data = $query->fetch_assoc();
$student_name = $user_data['username'] ?? 'Student';
$email = $user_data['email'] ?? 'N/A';
$course_code = $user_data['course_code'] ?? 'Not Mapped';
$semester = $user_data['current_semester'] ?? 'N/A';

// 2. Fetch Latest Announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 2");

// 3. Fetch Fee Status
$fee_query = $conn->query("SELECT * FROM fees WHERE student_id = $student_id");
$fee_data = $fee_query->fetch_assoc();

// 4. Fetch Attendance Stats
$att_total = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE student_id = $student_id")->fetch_assoc()['total'];
$att_present = $conn->query("SELECT COUNT(*) as present FROM attendance WHERE student_id = $student_id AND status = 'Present'")->fetch_assoc()['present'];
$att_percentage = $att_total > 0 ? round(($att_present / $att_total) * 100) : 0;

// 5. Fetch Recent Marks
$marks_query = $conn->query("SELECT m.marks_obtained, m.total_marks, s.subject_name FROM marks m JOIN subjects s ON m.subject_id = s.id WHERE m.student_id = $student_id LIMIT 4");

// 6. Fetch Today's Timetable
$current_day = date('l'); // Gets current day, e.g., 'Monday'
$timetable_query = $conn->query("SELECT * FROM timetables WHERE day_of_week = '$current_day' ORDER BY time_slot ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - SECMS</title>
    <style>
        :root {
            --primary: #0f172a;
            --accent: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-light: #64748b;
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #60a5fa; font-size: 20px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        
        /* Main Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 40px; box-sizing: border-box; overflow-y: auto; }
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        .subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 15px; }
        
        /* CSS Grid Layout for Dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }
        
        /* Card Styling */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        .card-header h2 { margin: 0; font-size: 18px; color: var(--primary); }
        
        /* Specific Component Styles */
        .announcement-item { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); }
        .announcement-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .announcement-title { font-weight: 600; color: var(--accent); margin-bottom: 5px; }
        
        .metric-value { font-size: 36px; font-weight: 700; margin: 10px 0; color: var(--primary); }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 0; border-bottom: 1px solid var(--border); text-align: left; font-size: 14px; }
        th { color: var(--text-light); font-weight: 600; }
        
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .bg-success { background: #d1fae5; color: #065f46; }
        .bg-warning { background: #fef3c7; color: #92400e; }
        
        /* Grid Spanning */
        .col-span-2 { grid-column: span 2; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Student</h3>
    <a href="student_dashboard.php" class="active">My Overview Portal</a>
    <a href="student_profile.php">My Profile Sheet</a>
    <a href="student_classmates.php">Classmates Directory</a>
    <a href="student_curriculum.php">Curriculum Roadmap</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: #64748b;">Academic Session: <strong>Semester <?php echo htmlspecialchars($semester); ?></strong></div>
        <div style="font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <div style="width: 30px; height: 30px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <?php echo strtoupper(substr($student_name, 0, 1)); ?>
            </div>
            <?php echo htmlspecialchars($student_name); ?>
        </div>
    </header>

    <main class="main-content">
        <h1>Welcome back, <?php echo htmlspecialchars($student_name); ?>!</h1>
        <div class="subtitle">Here is your academic overview for B.Tech (<?php echo htmlspecialchars($course_code); ?>).</div>

        <div class="dashboard-grid">
            
            <div class="card col-span-2">
                <div class="card-header">
                    <h2>📢 Latest Announcements</h2>
                </div>
                <?php if ($announcements && $announcements->num_rows > 0): ?>
                    <?php while($ann = $announcements->fetch_assoc()): ?>
                        <div class="announcement-item">
                            <div class="announcement-title"><?php echo htmlspecialchars($ann['title']); ?></div>
                            <div style="font-size: 14px; color: var(--text-light);"><?php echo htmlspecialchars($ann['message']); ?></div>
                            <div style="font-size: 12px; color: #94a3b8; margin-top: 5px;">Posted: <?php echo date('F d, Y', strtotime($ann['created_at'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">No new announcements at this time.</p>
                <?php endif; ?>
            </div>

            <div class="card" style="border-top: 4px solid var(--accent);">
                <div class="card-header">
                    <h2>📅 Attendance</h2>
                </div>
                <div class="metric-value"><?php echo $att_percentage; ?>%</div>
                <p style="font-size: 14px; color: var(--text-light); margin: 0;">
                    Present for <strong><?php echo $att_present; ?></strong> out of <strong><?php echo $att_total; ?></strong> total recorded sessions.
                </p>
                <?php if($att_percentage < 75 && $att_total > 0): ?>
                    <p style="color: var(--danger); font-size: 13px; font-weight: 600; margin-top: 10px;">⚠️ Warning: Below 75% threshold</p>
                <?php endif; ?>
            </div>

            <div class="card col-span-2">
                <div class="card-header">
                    <h2>📊 Recent Examination Grades</h2>
                </div>
                <?php if ($marks_query && $marks_query->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks Obtained</th>
                                <th>Total Marks</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($mark = $marks_query->fetch_assoc()): 
                                $percent = ($mark['marks_obtained'] / $mark['total_marks']) * 100;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($mark['subject_name']); ?></strong></td>
                                <td style="color: var(--primary); font-weight: 600;"><?php echo $mark['marks_obtained']; ?></td>
                                <td><?php echo $mark['total_marks']; ?></td>
                                <td>
                                    <?php if($percent >= 80): ?>
                                        <span class="status-badge bg-success">Excellent</span>
                                    <?php elseif($percent >= 50): ?>
                                        <span class="status-badge bg-warning" style="background: #e0f2fe; color: #0369a1;">Good</span>
                                    <?php else: ?>
                                        <span class="status-badge" style="background: #fee2e2; color: #991b1b;">Needs Improvement</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">No examination marks have been published yet.</p>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 25px;">
                
                <div class="card" style="border-top: 4px solid var(--success);">
                    <div class="card-header">
                        <h2>💳 Financial Ledger</h2>
                    </div>
                    <?php if ($fee_data): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                            <span style="color: var(--text-light);">Total Fee:</span>
                            <strong>₹<?php echo number_format($fee_data['total_amount'], 2); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px;">
                            <span style="color: var(--text-light);">Paid:</span>
                            <strong style="color: var(--success);">₹<?php echo number_format($fee_data['amount_paid'], 2); ?></strong>
                        </div>
                        <div style="border-top: 1px solid var(--border); padding-top: 10px; text-align: center;">
                            <span class="status-badge <?php echo ($fee_data['status'] == 'Paid') ? 'bg-success' : 'bg-warning'; ?>">
                                Status: <?php echo htmlspecialchars($fee_data['status']); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--text-light); font-size: 14px;">No fee structure assigned yet.</p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>🕒 Today's Schedule (<?php echo $current_day; ?>)</h2>
                    </div>
                    <?php if ($timetable_query && $timetable_query->num_rows > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php while($slot = $timetable_query->fetch_assoc()): ?>
                                <div style="background: #f1f5f9; padding: 12px; border-radius: 8px;">
                                    <div style="font-size: 12px; font-weight: 600; color: var(--accent); margin-bottom: 4px;"><?php echo htmlspecialchars($slot['time_slot']); ?></div>
                                    <div style="font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-light);">Room: <?php echo htmlspecialchars($slot['room_number']); ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--text-light); font-size: 14px;">No classes scheduled for today.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>