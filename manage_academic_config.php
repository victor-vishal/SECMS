<?php
include 'db_connect.php';
session_start();

// Security Gate Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_user = $_SESSION['username'] ?? 'Admin';
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
        $sql = "DELETE FROM courses WHERE course_code = '$course_code'";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='error'>Course '$course_code' and all its associated subjects purged successfully!</div>";
        }
    } elseif ($action === 'delete_batch') {
        $batch_id = intval($_GET['id']);
        $sql = "DELETE FROM batches WHERE id = $batch_id";
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='error'>Academic batch destroyed successfully!</div>";
        }
    } elseif ($action === 'delete_subject') {
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

// Fetch active structures
$courses_res = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
$batches_res = $conn->query("SELECT * FROM batches ORDER BY batch_name DESC");
$subjects_res = $conn->query("SELECT s.*, c.course_name FROM subjects s JOIN courses c ON s.course_code = c.course_code ORDER BY s.course_code, s.semester, s.subject_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Configuration - SECMS Admin</title>
    <style>
        :root {
            --primary: #4f46e5; --primary-dark: #3730a3; --accent: #6366f1; --success: #10b981; --danger: #ef4444;
            --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; --text-light: #64748b;
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #38bdf8; font-size: 20px; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .sidebar a.active { background: var(--primary); color: white; }
        
        /* Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 30px 40px; box-sizing: border-box; overflow-y: auto; }
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        .subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 15px; }
        
        /* CSS Grid Layouts */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 25px; }
        .col-span-2 { grid-column: span 2; }
        
        /* Cards & Forms */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .card-header { margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; font-size: 18px; font-weight: 700; color: var(--text); }
        
        label { font-size: 13px; font-weight: 600; color: var(--text-light); display: block; margin-top: 12px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;}
        input, select { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; font-family: inherit; }
        input:focus, select:focus { outline: 2px solid var(--primary); border-color: transparent; }
        
        button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; padding: 12px; border-radius: 6px; width: 100%; margin-top: 15px; transition: 0.2s; }
        button:hover { background: var(--primary-dark); }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid var(--border); text-align: left; font-size: 14px; }
        th { color: var(--text-light); font-weight: 600; background: #f8fafc; }
        
        .action-link { font-weight: 600; text-decoration: none; font-size: 13px; margin-right: 10px; }
        .text-danger { color: var(--danger); }
        .text-primary { color: var(--primary); }
        
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #34d399; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #f87171; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Admin</h3>
    <a href="admin_dashboard.php">Command Center</a>
    <a href="manage_academic_config.php" class="active">Academic Config</a>
    <a href="admin_assign_student.php">Assign Student Profiles</a>
    <a href="admin_profile.php">Profile Sheet</a>
    <a href="profile.php">Account Settings</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out System</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: var(--text-light);">System Role: <strong>Administrator</strong></div>
        
        <!-- Interactive Profile Menu -->
        <div class="profile-menu" style="position: relative; display: inline-block;">
            <div class="profile-trigger" style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 14px; border: 1px solid var(--border);" onclick="var d = document.getElementById('admin-drop'); d.style.display = d.style.display === 'block' ? 'none' : 'block';">
                <div style="width: 28px; height: 28px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                    <?php echo strtoupper(substr($admin_user, 0, 1)); ?>
                </div>
                <?php echo htmlspecialchars($admin_user); ?> ▾
            </div>
            
            <div id="admin-drop" style="display: none; position: absolute; right: 0; top: 48px; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid var(--border); z-index: 50; overflow: hidden;">
                <a href="profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px;">⚙️ Account Settings</a>
                <a href="logout.php" style="color: #ef4444; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-top: 1px solid #f1f5f9;">🚪 Sign Out</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <h1>Academic Structure Configurator</h1>
        <div class="subtitle">Initialize master programs, batches, and curriculum mappings.</div>

        <?php echo $message; ?>

        <div class="grid-2">
            <!-- Form 1: Add Course -->
            <div class="card" style="border-top: 4px solid var(--primary);">
                <div class="card-header">Create New Degree / Course</div>
                <form action="manage_academic_config.php" method="POST">
                    <label>Course Unique Code</label>
                    <input type="text" name="course_code" placeholder="e.g., CS, CD, IT, ME" required>
                    <label>Full Nomenclature Title</label>
                    <input type="text" name="course_name" placeholder="e.g., B.Tech in Computer Science" required>
                    <button type="submit" name="add_course">Register Course Program</button>
                </form>
            </div>

            <!-- Form 2: Add Batch -->
            <div class="card" style="border-top: 4px solid var(--success);">
                <div class="card-header">Initialize Graduation Batch</div>
                <form action="manage_academic_config.php" method="POST">
                    <label>Batch Identifier Frame</label>
                    <input type="text" name="batch_name" placeholder="e.g., 2024-2028" required>
                    <label>Current System Semester Phase</label>
                    <select name="current_semester" required>
                        <?php for($i=1; $i<=8; $i++): ?>
                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" name="add_batch" style="background: var(--success);">Initialize Batch</button>
                </form>
            </div>

            <!-- Form 3: Map Subject -->
            <div class="card col-span-2" style="border-top: 4px solid var(--warning);">
                <div class="card-header">Map Curricular Subject to Degree Program</div>
                <form action="manage_academic_config.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end;">
                    <div>
                        <label>Target Academic Program</label>
                        <select name="course_code" required>
                            <option value="">-- Choose Program --</option>
                            <?php 
                            if($courses_res && $courses_res->num_rows > 0) {
                                $courses_res->data_seek(0);
                                while($c = $courses_res->fetch_assoc()) {
                                    echo "<option value='".$c['course_code']."'>[".$c['course_code']."] ".$c['course_name']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Target Semester Phase</label>
                        <select name="semester" required>
                            <?php for($i=1; $i<=8; $i++): ?>
                                <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label>Unique Subject Code</label>
                        <input type="text" name="subject_code" placeholder="e.g., CS-402" required>
                    </div>
                    <div>
                        <label>Subject Title Description</label>
                        <input type="text" name="subject_name" placeholder="e.g., Database Management Systems" required>
                    </div>
                    <button type="submit" name="add_subject" style="grid-column: span 2; background: #d97706;">Map Curriculum Subject</button>
                </form>
            </div>
            
            <!-- Table 1: Courses -->
            <div class="card">
                <div class="card-header">Registered Degrees & Streams</div>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Degree Name</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($courses_res && $courses_res->num_rows > 0): $courses_res->data_seek(0); while($c = $courses_res->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($c['course_code']); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($c['course_name']); ?></strong></td>
                            <td style="text-align: right;">
                                <a href="manage_academic_config.php?action=delete_course&id=<?php echo urlencode($c['course_code']); ?>" class="action-link text-danger" onclick="return confirm('Deleting this course drops ALL mapped subjects! Proceed?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align:center; color:var(--text-light);">No courses found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table 2: Batches -->
            <div class="card">
                <div class="card-header">Active Academic Batches</div>
                <table>
                    <thead>
                        <tr>
                            <th>Batch Year</th>
                            <th>Current Term</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($batches_res && $batches_res->num_rows > 0): $batches_res->data_seek(0); while($b = $batches_res->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($b['batch_name']); ?></strong></td>
                            <td><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius:4px; font-weight:600; font-size:12px;">Sem <?php echo $b['current_semester']; ?></span></td>
                            <td style="text-align: right;">
                                <?php if($b['current_semester'] < 8): ?>
                                    <a href="manage_academic_config.php?action=promote_batch&id=<?php echo $b['id']; ?>" class="action-link text-primary">Promote</a>
                                <?php endif; ?>
                                <a href="manage_academic_config.php?action=delete_batch&id=<?php echo $b['id']; ?>" class="action-link text-danger" onclick="return confirm('Completely clear this batch?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align:center; color:var(--text-light);">No batches initialized.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table 3: Master Subjects (Spans full width) -->
            <div class="card col-span-2">
                <div class="card-header">Master Subjects Curriculum Map Index</div>
                <table>
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Assigned Stream</th>
                            <th>Term Phase</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($subjects_res && $subjects_res->num_rows > 0): while($sub = $subjects_res->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($sub['subject_code']); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($sub['subject_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($sub['course_name']); ?></td>
                            <td><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius:4px; font-weight:600; font-size:12px;">Sem <?php echo $sub['semester']; ?></span></td>
                            <td style="text-align: right;">
                                <a href="manage_academic_config.php?action=delete_subject&id=<?php echo $sub['id']; ?>" class="action-link text-danger" onclick="return confirm('Drop this subject entry from curriculum map?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--text-light); padding:20px;">No subjects systematically mapped yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>