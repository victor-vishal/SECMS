<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$faculty_name = $_SESSION['username'] ?? 'Faculty Member';
$message = "";
$students_res = null;
$selected_course = "";
$selected_batch = "";
$selected_subject_name = "";
$attendance_date = date('Y-m-d');

// Commit Attendance Sheet to Database
if (isset($_POST['save_attendance'])) {
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $attendance_date = mysqli_real_escape_string($conn, $_POST['attendance_date']);
    
    if (isset($_POST['student_ids'])) {
        $committed_count = 0;
        foreach ($_POST['student_ids'] as $student_id) {
            $student_id = intval($student_id);
            $status = (isset($_POST['attendance_status'][$student_id])) ? 'Present' : 'Absent';
            
            $check = $conn->query("SELECT id FROM attendance WHERE student_id = $student_id AND subject_name = '$subject_name' AND date = '$attendance_date'");
            
            if ($check->num_rows > 0) {
                $sql = "UPDATE attendance SET status = '$status' WHERE student_id = $student_id AND subject_name = '$subject_name' AND date = '$attendance_date'";
            } else {
                $sql = "INSERT INTO attendance (student_id, date, status, subject_name) VALUES ($student_id, '$attendance_date', '$status', '$subject_name')";
            }
            if ($conn->query($sql) === TRUE) {
                $committed_count++;
            }
        }
        $message = "<div class='success'>Successfully registered daily attendance ledger for $committed_count students!</div>";
    }
}

// Filter Class List Roster
if (isset($_GET['load_roster'])) {
    $selected_course = isset($_GET['course_code']) ? mysqli_real_escape_string($conn, $_GET['course_code']) : "";
    $selected_batch = isset($_GET['batch_id']) ? intval($_GET['batch_id']) : 0;
    $selected_subject_name = isset($_GET['subject_name']) ? mysqli_real_escape_string($conn, $_GET['subject_name']) : "";
    $attendance_date = isset($_GET['attendance_date']) ? mysqli_real_escape_string($conn, $_GET['attendance_date']) : date('Y-m-d');
    
    if (!empty($selected_course) && !empty($selected_batch)) {
        $students_res = $conn->query("SELECT id, username, email FROM users WHERE role='student' AND status='approved' AND course_code='$selected_course' AND batch_id=$selected_batch ORDER BY username ASC");
    }
}

$courses = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Attendance - Faculty SECMS</title>
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
        
        .grid-filter { display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 15px; align-items: end; }
        label { font-size: 13px; font-weight: 600; color: var(--text-light); display: block; margin-bottom: 6px; }
        select, input { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { background: var(--accent); color: white; border: none; font-weight: 600; cursor: pointer; padding: 10px 20px; border-radius: 6px; transition: 0.2s; }
        button:hover { background: #047857; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid var(--border); text-align: left; }
        th { background: #f8fafc; color: var(--text-light); font-weight: 600; }
        .checkbox-container { transform: scale(1.3); cursor: pointer; accent-color: var(--accent); }
        
        .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #34d399; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php">Overview Dashboard</a>
    <a href="faculty_marks.php">Manage Grades/Marks</a>
    <a href="faculty_curriculum.php">Course Curriculum Directory</a>
    <a href="faculty_attendance.php" class="active">Track Daily Attendance</a>
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
        <h1>Track Daily Attendance Logs</h1>
        <div class="subtitle">Filter by stream and batch to load the interactive student roster.</div>
        
        <?php if (!empty($message)) echo $message; ?>

        <div class="card">
            <form action="faculty_attendance.php" method="GET" class="grid-filter">
                <div>
                    <label>Course Stream:</label>
                    <select name="course_code" required>
                        <option value="">-- Stream --</option>
                        <?php while($c = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $c['course_code']; ?>" <?php if($selected_course == $c['course_code']) echo 'selected'; ?>><?php echo $c['course_code']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Academic Batch:</label>
                    <select name="batch_id" required>
                        <option value="">-- Batch --</option>
                        <?php while($b = $batches->fetch_assoc()): ?>
                            <option value="<?php echo $b['id']; ?>" <?php if($selected_batch == $b['id']) echo 'selected'; ?>><?php echo $b['batch_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Subject Track Name:</label>
                    <select name="subject_name" required>
                        <option value="">-- Select Subject --</option>
                        <?php 
                        if (!empty($selected_course)) {
                            $subs = $conn->query("SELECT subject_name FROM subjects WHERE course_code = '$selected_course' ORDER BY semester ASC");
                            while($s = $subs->fetch_assoc()) {
                                $sel = ($selected_subject_name == $s['subject_name']) ? 'selected' : '';
                                echo "<option value='".htmlspecialchars($s['subject_name'])."' $sel>".htmlspecialchars($s['subject_name'])."</option>";
                            }
                        } else {
                            $subs = $conn->query("SELECT DISTINCT subject_name FROM subjects ORDER BY subject_name ASC");
                            while($s = $subs->fetch_assoc()) {
                                echo "<option value='".htmlspecialchars($s['subject_name'])."'>".htmlspecialchars($s['subject_name'])."</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label>Session Date:</label>
                    <input type="date" name="attendance_date" value="<?php echo $attendance_date; ?>" required>
                </div>
                <button type="submit" name="load_roster">Load Class Roster</button>
            </form>
        </div>

        <?php if ($students_res): ?>
        <div class="card" style="border-top: 4px solid var(--accent);">
            <h2 style="margin-top: 0; font-size: 18px; margin-bottom: 20px;">Roster Attendance Sheet (<?php echo htmlspecialchars($attendance_date); ?>)</h2>
            <form action="faculty_attendance.php" method="POST">
                <input type="hidden" name="subject_name" value="<?php echo htmlspecialchars($selected_subject_name); ?>">
                <input type="hidden" name="attendance_date" value="<?php echo $attendance_date; ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Student Registered Name</th>
                            <th>Email Identification</th>
                            <th style="text-align: center;">Status (Checked = Present)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($students_res->num_rows > 0): 
                            while($row = $students_res->fetch_assoc()):
                                $is_checked = "checked";
                                $history_check = $conn->query("SELECT status FROM attendance WHERE student_id = ".$row['id']." AND subject_name = '".mysqli_real_escape_string($conn, $selected_subject_name)."' AND date = '$attendance_date'");
                                if ($history_check && $history_check->num_rows > 0) {
                                    $is_checked = (strtolower($history_check->fetch_assoc()['status']) === 'present') ? "checked" : "";
                                }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($row['email']); ?></code></td>
                            <td style="text-align: center;">
                                <input type="hidden" name="student_ids[]" value="<?php echo $row['id']; ?>">
                                <input type="checkbox" name="attendance_status[<?php echo $row['id']; ?>]" class="checkbox-container" value="present" <?php echo $is_checked; ?>>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 30px;">No students enrolled here.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($students_res->num_rows > 0): ?>
                    <button type="submit" name="save_attendance" style="margin-top: 25px;">Save Daily Attendance Ledger</button>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>