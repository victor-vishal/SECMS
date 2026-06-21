<?php
include 'db_connect.php';
session_start();

// Security check: Only faculty allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

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
            
            if ($marks_obtained !== '') {
                $marks_obtained = intval($marks_obtained);
                
                // Check if marks already exist for this student and subject (to update instead of duplicate)
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
        // Query approved students matching selected course and batch
        $students_res = $conn->query("SELECT id, username, email FROM users WHERE role='student' AND status='approved' AND course_code='$selected_course' AND batch_id=$selected_batch ORDER BY username ASC");
    }
}

// Fetch general dropdown selectors
$courses = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Panel - Marks Entry</title>
    <style>
        :root { --primary: #4f46e5; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; color: #38bdf8; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 30px; }
        .form-inline { display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 15px; align-items: end; }
        select, input, button { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; }
        button:hover { background: #4338ca; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left; }
        th, td { padding: 12px; border-bottom: 1px solid var(--border); }
        th { background: #f1f5f9; color: #475569; }
        .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .marks-input { width: 100px; text-align: center; margin: 0; }
    </style>
    <script>
        function loadSubjects(courseCode) {
            if (courseCode === "") {
                document.getElementById("subject_selector").innerHTML = '<option value="">-- Select Subject --</option>';
                return;
            }
            
            // Native AJAX request to fetch dynamic subjects seamlessly
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
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <h1>Student Curricular Performance Entry</h1>
    <?php if (!empty($message)) echo $message; ?>

    <div class="card">
        <form action="faculty_marks.php" method="GET" class="form-inline">
            <div>
                <label style="font-size:12px; font-weight:600;">Course Stream:</label>
                <select name="course_code" onchange="loadSubjects(this.value)" required>
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
                <label style="font-size:12px; font-weight:600;">Subject Track:</label>
                <select name="subject_id" id="subject_selector" required>
                    <option value="">-- Select Subject --</option>
                    <?php 
                    if (!empty($selected_course)) {
                        $subs = $conn->query("SELECT id, subject_code, subject_name, semester FROM subjects WHERE course_code = '$selected_course' ORDER BY semester ASC");
                        while($s = $subs->fetch_assoc()) {
                            $sel = ($selected_subject == $s['id']) ? 'selected' : '';
                            echo "<option value='".$s['id']."' $sel>[Sem ".$s['semester']."] ".$s['subject_code']."</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <button type="submit" name="filter_students" style="padding: 10px 20px;">Load Class List</button>
        </form>
    </div>

    <?php if ($students_res): ?>
        <div class="card">
            <h2>Evaluation Spreadsheet Grid</h2>
            <form action="faculty_marks.php" method="POST">
                <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                
                <div style="margin-bottom: 15px; max-width: 200px;">
                    <label for="total_marks" style="font-size: 13px; font-weight: 600;">Maximum Target Marks:</label>
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
                                // Look up existing marks if already saved previously
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
                                    <input type="number" 
                                           name="marks[<?php echo $row['id']; ?>]" 
                                           class="marks-input" 
                                           placeholder="e.g. 85" 
                                           value="<?php echo $prev_marks; ?>"
                                           min="0" max="100">
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #94a3b8; padding: 30px;">No approved students currently registered under this selected track combination.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($students_res->num_rows > 0): ?>
                    <button type="submit" name="submit_marks" style="margin-top: 20px; max-width: 250px;">Commit Grades Spreadsheet</button>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>