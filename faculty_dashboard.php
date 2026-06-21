<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];
$faculty_name = $_SESSION['username'] ?? 'Faculty Member';

// 1. Fetch Quick Stats
$total_students = $conn->query("SELECT COUNT(id) as count FROM users WHERE role='student' AND status='approved'")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(course_code) as count FROM courses")->fetch_assoc()['count'];
$total_subjects = $conn->query("SELECT COUNT(id) as count FROM subjects")->fetch_assoc()['count'];

// 2. Fetch Latest Announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 2");

// 3. Fetch Today's Timetable
$current_day = date('l'); // e.g., 'Monday'
$timetable_query = $conn->query("SELECT * FROM timetables WHERE day_of_week = '$current_day' ORDER BY time_slot ASC");

// 4. Fetch Grading Activity Analytics (Shows how many students have been graded per subject)
$grading_activity = $conn->query("
    SELECT s.subject_code, s.subject_name, COUNT(m.id) as graded_students 
    FROM marks m 
    JOIN subjects s ON m.subject_id = s.id 
    GROUP BY s.id, s.subject_code, s.subject_name 
    ORDER BY graded_students DESC 
    LIMIT 4
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Dashboard - SECMS</title>
    <style>
        :root {
            --primary: #0f172a;
            --accent: #059669; /* Emerald Green for Faculty */
            --accent-hover: #047857;
            --success: #10b981;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-light: #64748b;
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #34d399; font-size: 20px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; }
        .sidebar a.active { background: var(--accent); }
        
        /* Main Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 40px; box-sizing: border-box; overflow-y: auto; }
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        .subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 15px; }
        
        /* CSS Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }
        
        /* Card Styling */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
        .card-header h2 { margin: 0; font-size: 18px; color: var(--primary); }
        
        /* Metric Stat Cards */
        .stat-card { background: var(--card); border: 1px solid var(--border); padding: 25px; border-radius: 12px; display: flex; flex-direction: column; }
        .stat-card p { margin: 0; font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--text-light); letter-spacing: 0.5px; }
        .stat-card h3 { margin: 10px 0 0 0; font-size: 32px; font-weight: 700; color: var(--primary); }
        
        /* Specific Component Styles */
        .announcement-item { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed var(--border); }
        .announcement-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .announcement-title { font-weight: 600; color: var(--accent); margin-bottom: 5px; }
        
        .action-btn { display: block; width: 100%; text-align: center; padding: 12px; background: #f1f5f9; color: var(--primary); text-decoration: none; border-radius: 8px; font-weight: 600; margin-bottom: 10px; transition: 0.2s; border: 1px solid var(--border); }
        .action-btn:hover { background: var(--accent); color: white; border-color: var(--accent); }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid var(--border); text-align: left; font-size: 14px; }
        th { color: var(--text-light); font-weight: 600; background: #f8fafc; }
        
        /* Grid Spanning */
        .col-span-2 { grid-column: span 2; }
        .col-span-3 { grid-column: span 3; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php" class="active">Overview Dashboard</a>
    <a href="faculty_marks.php">Manage Grades/Marks</a>
    <a href="faculty_curriculum.php">Course Curriculum Directory</a>
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
        <h1>Faculty Performance Overview</h1>
        <div class="subtitle">Welcome back, Professor. Here is an overview of the current academic batch metrics.</div>

        <div class="dashboard-grid">
            
            <div class="stat-card" style="border-left: 4px solid var(--accent);">
                <p>Approved Enrollments</p>
                <h3><?php echo $total_students; ?> Students</h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--info);">
                <p>Active Course Branches</p>
                <h3><?php echo $total_courses; ?> Streams</h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--warning);">
                <p>Mapped Curriculum Modules</p>
                <h3><?php echo $total_subjects; ?> Subjects</h3>
            </div>

            <div class="card col-span-2">
                <div class="card-header">
                    <h2>📢 Campus Bulletins</h2>
                </div>
                <?php if ($announcements && $announcements->num_rows > 0): ?>
                    <?php while($ann = $announcements->fetch_assoc()): ?>
                        <div class="announcement-item">
                            <div class="announcement-title"><?php echo htmlspecialchars($ann['title']); ?></div>
                            <div style="font-size: 14px; color: var(--text-light);"><?php echo htmlspecialchars($ann['message']); ?></div>
                            <div style="font-size: 12px; color: #94a3b8; margin-top: 5px;">Posted by <?php echo htmlspecialchars($ann['created_by']); ?> on <?php echo date('F d, Y', strtotime($ann['created_at'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">No active announcements.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>⚡ Quick Actions</h2>
                </div>
                <a href="faculty_attendance.php" class="action-btn">📝 Roll Call Attendance</a>
                <a href="faculty_marks.php" class="action-btn">📊 Upload Exam Grades</a>
                <a href="faculty_curriculum.php" class="action-btn">📚 View Blueprints</a>
                <a href="faculty_profile.php" class="action-btn" style="margin-bottom: 0;">⚙️ Account Settings</a>
            </div>

            <div class="card col-span-2">
                <div class="card-header">
                    <h2>📈 Grading Progress Matrix</h2>
                </div>
                <?php if ($grading_activity && $grading_activity->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Descriptive Nomenclature</th>
                                <th>Total Students Graded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($activity = $grading_activity->fetch_assoc()): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($activity['subject_code']); ?></code></td>
                                <td><strong><?php echo htmlspecialchars($activity['subject_name']); ?></strong></td>
                                <td>
                                    <span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 13px;">
                                        <?php echo $activity['graded_students']; ?> Records
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">No grading data has been committed to the ledger yet.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>🕒 Campus Schedule (<?php echo $current_day; ?>)</h2>
                </div>
                <?php if ($timetable_query && $timetable_query->num_rows > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php while($slot = $timetable_query->fetch_assoc()): ?>
                            <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; border-left: 3px solid var(--accent);">
                                <div style="font-size: 12px; font-weight: 600; color: var(--text-light); margin-bottom: 4px;"><?php echo htmlspecialchars($slot['time_slot']); ?></div>
                                <div style="font-size: 14px; font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                <div style="font-size: 12px; color: var(--accent); margin-top: 4px; font-weight: 500;">Room: <?php echo htmlspecialchars($slot['room_number']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">No campus classes scheduled for today.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

</body>
</html>