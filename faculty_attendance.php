<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$message = "";
$students_res = null;
$selected_course = "";
$selected_batch = "";
$selected_subject_name = "";
$attendance_date = date('Y-m-d');

// Commit Attendance Sheet to Database using your exact columns
if (isset($_POST['save_attendance'])) {
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $attendance_date = mysqli_real_escape_string($conn, $_POST['attendance_date']);
    
    if (isset($_POST['student_ids'])) {
        $committed_count = 0;
        foreach ($_POST['student_ids'] as $student_id) {
            $student_id = intval($student_id);
            $status = (isset($_POST['attendance_status'][$student_id])) ? 'Present' : 'Absent';
            
            // Match your exact column names: student_id, date, status, subject_name
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
    <title>Faculty Panel - Track Attendance</title>
    <style>
        :root { --primary: #059669; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; color: #38bdf8; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 30px; }
        .grid-filter { display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 15px; align-items: end; }
        select, input, button { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; }
        button:hover { background: #047857; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border); }
        th { background: #f1f5f9; color: #475569; }
        .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .checkbox-container { transform: scale(1.3); cursor: pointer; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php">Overview Dashboard</a>
    <a href="faculty_marks.php">Manage Grades/Marks</a>
    <a href="faculty_curriculum.php">Course Curriculum Directory</a>
    <a href="faculty_attendance.php" class="active">Track Daily Attendance</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <h1>Track Daily Attendance Logs</h1>
    <?php if (!empty($message)) echo $message; ?>

    <div class="card">
        <form action="faculty_attendance.php" method="GET" class="grid-filter">
            <div>
                <label style="font-size:12px; font-weight:600;">Course Stream:</label>
                <select name="course_code" required>
                    <option value="">-- Stream --</option>
                    <?php while($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $c['course_code']; ?>" <?php if($selected_course == $c['course_code']) echo 'selected'; ?>>
                            <?php echo $c['course_code']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label style="font-size:12px; font-weight:600;">Academic Batch:</label>
                <select name="batch_id" required>
                    <option value="">-- Batch --</option>
                    <?php while($b = $batches->fetch_assoc()): ?>
                        <option value="<?php echo $b['id']; ?>" <?php if($selected_batch == $b['id']) echo 'selected'; ?>>
                            <?php echo $b['batch_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label style="font-size:12px; font-weight:600;">Subject Track Name:</label>
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
                        // Backup pull of all distinct names if no course selected yet
                        $subs = $conn->query("SELECT DISTINCT subject_name FROM subjects ORDER BY subject_name ASC");
                        while($s = $subs->fetch_assoc()) {
                            echo "<option value='".htmlspecialchars($s['subject_name'])."'>".htmlspecialchars($s['subject_name'])."</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div>
                <label style="font-size:12px; font-weight:600;">Session Date:</label>
                <input type="date" name="attendance_date" value="<?php echo $attendance_date; ?>" required>
            </div>

            <button type="submit" name="load_roster" style="padding: 10px 15px;">Load Class</button>
        </form>
    </div>

    <?php if ($students_res): ?>
        <div class="card">
            <h2>Roster Attendance Sheet (<?php echo htmlspecialchars($attendance_date); ?>)</h2>
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
                                
                                // Checking history using your custom table layout
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
                                    <input type="checkbox" 
                                           name="attendance_status[<?php echo $row['id']; ?>]" 
                                           class="checkbox-container" 
                                           value="present" <?php echo $is_checked; ?>>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #94a3b8; padding: 30px;">No students enrolled here.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($students_res->num_rows > 0): ?>
                    <button type="submit" name="save_attendance" style="margin-top: 20px; max-width: 250px;">Save Daily Attendance Sheet</button>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>