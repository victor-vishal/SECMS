<?php
include 'db_connect.php';
session_start();

// Security Gate Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. Handle New Course Insertion
if (isset($_POST['add_course'])) {
    $course_code = mysqli_real_escape_string($conn, strtoupper(trim($_POST['course_code'])));
    $course_name = mysqli_real_escape_string($conn, trim($_POST['course_name']));

    $sql = "INSERT INTO courses (course_code, course_name) VALUES ('$course_code', '$course_name')";
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='success'>Course '$course_code' registered successfully!</div>";
    } else {
        $message = "<div class='error'>Error adding course: " . $conn->error . "</div>";
    }
}

// 2. Handle New Batch Insertion
if (isset($_POST['add_batch'])) {
    $batch_name = mysqli_real_escape_string($conn, trim($_POST['batch_name']));
    $current_sem = intval($_POST['current_semester']);

    $sql = "INSERT INTO batches (batch_name, current_semester) VALUES ('$batch_name', $current_sem)";
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='success'>Academic batch '$batch_name' initialized!</div>";
    } else {
        $message = "<div class='error'>Error initializing batch: " . $conn->error . "</div>";
    }
}

// 3. Handle New Subject Mapping
if (isset($_POST['add_subject'])) {
    $sub_code = mysqli_real_escape_string($conn, strtoupper(trim($_POST['subject_code'])));
    $sub_name = mysqli_real_escape_string($conn, trim($_POST['subject_name']));
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $semester = intval($_POST['semester']);

    $sql = "INSERT INTO subjects (subject_code, subject_name, course_code, semester) VALUES ('$sub_code', '$sub_name', '$course_code', $semester)";
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='success'>Subject '$sub_name' mapped to Semester $semester successfully!</div>";
    } else {
        $message = "<div class='error'>Error mapping subject: " . $conn->error . "</div>";
    }
}

// 4. Handle Depletions / Deletions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    
    if ($action === 'delete_course') {
        $course_code = mysqli_real_escape_string($conn, $_GET['id']);
        // Due to ON DELETE CASCADE, deleting a course automatically drops its mapped subjects!
        $sql = "DELETE FROM courses WHERE course_code = '$course_code'";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='error'>Course '$course_code' and all its associated subjects purged successfully!</div>";
        }
    } 
    elseif ($action === 'delete_batch') {
        $batch_id = intval($_GET['id']);
        $sql = "DELETE FROM batches WHERE id = $batch_id";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='error'>Academic batch destroyed successfully!</div>";
        }
    }
    elseif ($action === 'delete_subject') {
        $sub_id = intval($_GET['id']);
        $sql = "DELETE FROM subjects WHERE id = $sub_id";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='error'>Subject curriculum entry dropped cleanly.</div>";
        }
    }
}

// 5. Handle Semester Promotions / Updates for Batches
if (isset($_GET['action']) && $_GET['action'] === 'promote_batch') {
    $batch_id = intval($_GET['id']);
    $sql = "UPDATE batches SET current_semester = current_semester + 1 WHERE id = $batch_id AND current_semester < 8";
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='success'>Batch academic level promoted to the next semester phase!</div>";
    }
}

// Fetch active structures for populating selectors & summary grids
$courses_res = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
$batches_res = $conn->query("SELECT * FROM batches ORDER BY batch_name DESC");
$subjects_res = $conn->query("SELECT s.*, c.course_name FROM subjects s JOIN courses c ON s.course_code = c.course_code ORDER BY s.course_code, s.semester, s.subject_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS Admin - Academic Configurations</title>
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #0f172a;
            --border: #e2e8f0;
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; }
        .sidebar h3 { margin: 0 0 30px 0; font-size: 20px; color: #38bdf8; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        
        .main-content { flex: 1; padding: 40px; box-sizing: border-box; overflow-y: auto; }
        .panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 35px; }
        .panel { background: var(--card-bg); padding: 25px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .panel h2 { margin-top: 0; font-size: 18px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px; color: #1e293b; }
        
        input, select, button { width: 100%; padding: 10px; margin-top: 6px; margin-bottom: 15px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; margin-top: 5px; }
        button:hover { background: #4338ca; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; margin-top: 10px; }
        th { padding: 10px; background: #f1f5f9; color: #475569; font-weight: 600; }
        td { padding: 10px; border-bottom: 1px solid var(--border); }
        .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Admin</h3>
    <a href="admin_dashboard.php">Dashboard Workspace</a>
    <a href="manage_academic_config.php" class="active">Academic Config</a>
    <a href="profile.php">My Account Profile</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out</a>
</div>

<div class="main-content">
    <h1>Academic Structure Configurator</h1>
    <?php if (!empty($message)) echo $message; ?>

    <div class="panel-grid">
        <div class="panel">
            <h2>Create New Academic Degree/Course</h2>
            <form action="manage_academic_config.php" method="POST">
                <label for="course_code">Course Unique Code:</label>
                <input type="text" name="course_code" id="course_code" placeholder="e.g., CS, CD, IT, ME" required>
                
                <label for="course_name">Full Course Nomenclature Title:</label>
                <input type="text" name="course_name" id="course_name" placeholder="e.g., Bachelor of Technology in CS" required>
                
                <button type="submit" name="add_course">Register Course</button>
            </form>
        </div>

        <div class="panel">
            <h2>Initialize Academic Graduation Batch</h2>
            <form action="manage_academic_config.php" method="POST">
                <label for="batch_name">Batch Identifier Frame:</label>
                <input type="text" name="batch_name" id="batch_name" placeholder="e.g., 2024-2028" required>
                
                <label for="current_semester">Current System Semester Phase:</label>
                <select name="current_semester" id="current_semester" required>
                    <?php for($i=1; $i<=8; $i++): ?>
                        <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                
                <button type="submit" name="add_batch">Initialize Batch</button>
            </form>
        </div>
    </div>

    <div class="panel" style="margin-bottom: 35px; max-width: 700px;">
        <h2>Map Curricular Subject (Semester & Program-wise)</h2>
        <form action="manage_academic_config.php" method="POST">
            <label for="course_code_select">Target Academic Degree Program:</label>
            <select name="course_code" id="course_code_select" required>
                <option value="">-- Choose Program --</option>
                <?php 
                if($courses_res->num_rows > 0) {
                    $courses_res->data_seek(0);
                    while($c = $courses_res->fetch_assoc()) {
                        echo "<option value='".$c['course_code']."'>[".$c['course_code']."] ".$c['course_name']."</option>";
                    }
                }
                ?>
            </select>

            <label for="sub_semester">Target Assessment Semester Phase:</label>
            <select name="semester" id="sub_semester" required>
                <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <label for="subject_code">Unique Subject Code:</label>
            <input type="text" name="subject_code" id="subject_code" placeholder="e.g., CS-402, MAS-101" required>

            <label for="subject_name">Subject Title Description:</label>
            <input type="text" name="subject_name" id="subject_name" placeholder="e.g., Database Management Systems" required>

            <button type="submit" name="add_subject">Map Curriculum Subject</button>
        </form>
    </div>

    <div class="panel-grid">
        <div class="panel">
            <h2>Registered Degrees & Streams</h2>
            <table>
                <thead>
                    <tr>
                        <td><code><?php echo htmlspecialchars($c['course_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($c['course_name']); ?></strong></td>
                        <td style="text-align: right;">
                            <a href="manage_academic_config.php?action=delete_course&id=<?php echo urlencode($c['course_code']); ?>" style="color: var(--danger); text-decoration: none; font-weight: 600;" onclick="return confirm('Deleting this course will instantly clear out all its mapped subjects! Proceed?')">Delete</a>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($courses_res && $courses_res->num_rows > 0):
                        $courses_res->data_seek(0); // Reset pointer to loop again
                        while($c = $courses_res->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($c['course_code']); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($c['course_name']); ?></strong></td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr><td colspan="2" style="text-align:center; color:#94a3b8;">No courses found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Active Academic Batches</h2>
            <table>
                <thead>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($b['batch_name']); ?></strong></td>
                        <td><span style="background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius:4px; font-weight:600; font-size:12px;">Semester <?php echo $b['current_semester']; ?></span></td>
                        <td style="text-align: right;">
                            <?php if($b['current_semester'] < 8): ?>
                                <a href="manage_academic_config.php?action=promote_batch&id=<?php echo $b['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; margin-right: 12px;">Promote Sem</a>
                            <?php endif; ?>
                            <a href="manage_academic_config.php?action=delete_batch&id=<?php echo $b['id']; ?>" style="color: var(--danger); text-decoration: none; font-weight: 600;" onclick="return confirm('Completely clear this batch configuration frame?')">Delete</a>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($batches_res && $batches_res->num_rows > 0):
                        $batches_res->data_seek(0); // Reset pointer to loop again
                        while($b = $batches_res->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($b['batch_name']); ?></strong></td>
                            <td><span style="background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius:4px; font-weight:600; font-size:12px;">Semester <?php echo $b['current_semester']; ?></span></td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr><td colspan="2" style="text-align:center; color:#94a3b8;">No batches initialized.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h2>Master Subjects Curriculum Map Index</h2>
        <table>
            <thead>
                <tr>
                    <td><code><?php echo htmlspecialchars($sub['subject_code']); ?></code></td>
                    <td><strong><?php echo htmlspecialchars($sub['subject_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($sub['course_name']); ?></td>
                    <td><span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius:4px; font-weight:600; font-size:12px;">Sem <?php echo $sub['semester']; ?></span></td>
                    <td style="text-align: right;">
                        <a href="manage_academic_config.php?action=delete_subject&id=<?php echo $sub['id']; ?>" style="color: var(--danger); text-decoration: none; font-weight: 600;" onclick="return confirm('Drop this subject entry from curriculum map?')">Delete</a>
                    </td>
                </tr>
            </thead>
            <tbody>
                <?php if ($subjects_res && $subjects_res->num_rows > 0): ?>
                    <?php while($sub = $subjects_res->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($sub['subject_code']); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($sub['subject_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($sub['course_name']); ?></td>
                            <td><span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius:4px; font-weight:600; font-size:12px;">Sem <?php echo $sub['semester']; ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; color:#94a3b8; padding:20px;">No subjects systematically mapped to active course curriculums yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>