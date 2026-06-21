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
$selected_subject = "";

// Handle Marks Submission
if (isset($_POST['submit_marks'])) {
    $subject_id = intval($_POST['subject_id']);
    $total_marks = intval($_POST['total_marks']);
    
    if (!empty($_POST['marks'])) {
        $success_count = 0;
        foreach ($_POST['marks'] as $student_id => $marks_obtained) {
            $student_id = intval($student_id);
            if ($marks_obtained !== "") {
                $marks_obtained = intval($marks_obtained);
                $check = $conn->query("SELECT id FROM marks WHERE student_id = $student_id AND subject_id = $subject_id");
                
                if ($check->num_rows > 0) {
                    $sql = "UPDATE marks SET marks_obtained = $marks_obtained, total_marks = $total_marks WHERE student_id = $student_id AND subject_id = $subject_id";
                } else {
                    $sql = "INSERT INTO marks (student_id, subject_id, marks_obtained, total_marks) VALUES ($student_id, $subject_id, $marks_obtained, $total_marks)";
                }
                if ($conn->query($sql) === TRUE) {
                    $success_count++;
                }
            }
        }
        $message = "<div class='success'>Successfully recorded marks for $success_count students!</div>";
    }
}

// Handle Student Filtering Search
if (isset($_GET['filter_students'])) {
    $selected_course = mysqli_real_escape_string($conn, $_GET['course_code']);
    $selected_batch = intval($_GET['batch_id']);
    $selected_subject = intval($_GET['subject_id']);
    
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
    <title>Grade Management - Faculty SECMS</title>
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
        
        .marks-input { width: 100px; text-align: center; margin: 0; border: 1px solid var(--border); border-radius: 4px; padding: 8px; font-weight: 600; color: var(--primary); }
        .marks-input:focus { outline: 2px solid var(--accent); border-color: transparent; }
        
        .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #34d399; }
    </style>
    
    <script>
    function loadSubjects(courseCode) {
        if (courseCode === "") {
            document.getElementById("subject_selector").innerHTML = '<option value="">-- Select Subject --</option>';
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_subjects.php?course_code=" + courseCode, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("subject_selector").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
    </script>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Faculty</h3>
    <a href="faculty_dashboard.php">Overview Dashboard</a>
    <a href="faculty_marks.php" class="active">Manage Grades/Marks</a>
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
        <h1>Student Curricular Performance Entry</h1>
        <div class="subtitle">Assign grades to individual academic cohorts.</div>
        
        <?php if (!empty($message)) echo $message; ?>

        <div class="card">
            <form action="faculty_marks.php" method="GET" class="grid-filter">
                <div>
                    <label>Course Stream:</label>
                    <select name="course_code" onchange="loadSubjects(this.value)" required>
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
                <div style="grid-column: span 2;">
                    <label>Subject Track:</label>
                    <select name="subject_id" id="subject_selector" required>
                        <option value="">-- Select Subject --</option>
                        <?php
                        if (!empty($selected_course)) {
                            $subs = $conn->query("SELECT id, subject_code, subject_name, semester FROM subjects WHERE course_code = '$selected_course' ORDER BY semester ASC");
                            while($s = $subs->fetch_assoc()) {
                                $sel = ($selected_subject == $s['id']) ? 'selected' : '';
                                echo "<option value='".$s['id']."' $sel>[Sem ".$s['semester']."] ".$s['subject_code']." - ".$s['subject_name']."</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="filter_students">Load Class List</button>
            </form>
        </div>

        <?php if ($students_res): ?>
        <div class="card" style="border-top: 4px solid var(--accent);">
            <h2 style="margin-top: 0; font-size: 18px; margin-bottom: 20px;">Evaluation Spreadsheet Grid</h2>
            <form action="faculty_marks.php" method="POST">
                <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                
                <div style="margin-bottom: 25px; max-width: 250px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border);">
                    <label for="total_marks" style="margin-top: 0;">Maximum Target Marks:</label>
                    <input type="number" name="total_marks" id="total_marks" value="100" required>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email Address</th>
                            <th>Marks Awarded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($students_res->num_rows > 0): 
                            while($row = $students_res->fetch_assoc()):
                                $prev_marks = "";
                                $mark_check = $conn->query("SELECT marks_obtained FROM marks WHERE student_id = ".$row['id']." AND subject_id = $selected_subject");
                                if($mark_check->num_rows > 0) {
                                    $prev_marks = $mark_check->fetch_assoc()['marks_obtained'];
                                }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($row['email']); ?></code></td>
                            <td>
                                <input type="number" name="marks[<?php echo $row['id']; ?>]" class="marks-input" placeholder="e.g. 85" value="<?php echo $prev_marks; ?>" min="0" max="100">
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 30px;">No approved students currently registered under this selected track combination.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($students_res->num_rows > 0): ?>
                    <button type="submit" name="submit_marks" style="margin-top: 25px;">Commit Grades Spreadsheet</button>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>