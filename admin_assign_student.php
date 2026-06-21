<?php
include 'db_connect.php';
session_start();

// Security check: Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_user = $_SESSION['username'] ?? 'Admin';
$message = "";

// Handle updating the student's academic profile metadata
if (isset($_POST['update_student'])) {
    $user_id = intval($_POST['user_id']);
    $course_code = !empty($_POST['course_code']) ? "'" . mysqli_real_escape_string($conn, $_POST['course_code']) . "'" : "NULL";
    $batch_id = !empty($_POST['batch_id']) ? intval($_POST['batch_id']) : "NULL";
    
    $update_sql = "UPDATE users SET course_code = $course_code, batch_id = $batch_id WHERE id = $user_id AND role = 'student'";
    
    if ($conn->query($update_sql) === TRUE) {
        $message = "<div class='success'>Student academic profile updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating profile: " . $conn->error . "</div>";
    }
}

// Fetch all approved students to display in an assignment selector dropdown
$students = $conn->query("SELECT id, username, email, course_code, batch_id FROM users WHERE role='student' AND status='approved' ORDER BY username ASC");
$courses = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Student Profiles - SECMS Admin</title>
    <style>
        :root {
            --primary: #4f46e5; --primary-dark: #3730a3; --accent: #6366f1; --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
            --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #0f172a; --text-light: #64748b;
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #38bdf8; font-size: 20px; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .sidebar a.active { background: var(--primary); color: white; border-left: 4px solid #818cf8; padding-left: 11px; }
        
        /* Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 30px 40px; box-sizing: border-box; overflow-y: auto; }
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        .subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 15px; }
        
        /* Cards & Forms */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); max-width: 600px; }
        .card-header { margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; font-size: 18px; font-weight: 700; color: var(--text); }
        
        label { font-size: 13px; font-weight: 600; color: var(--text-light); display: block; margin-top: 15px; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
        select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; font-family: inherit; }
        select:focus { outline: 2px solid var(--primary); border-color: transparent; }
        
        button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; padding: 14px; border-radius: 6px; width: 100%; margin-top: 25px; font-size: 15px; transition: 0.2s; }
        button:hover { background: var(--primary-dark); }
        
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #34d399; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; border: 1px solid #f87171; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Admin</h3>
    <a href="admin_dashboard.php">Command Center</a>
    <a href="manage_academic_config.php">Academic Config</a>
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
            
            <!-- Dropdown Content -->
            <div id="admin-drop" style="display: none; position: absolute; right: 0; top: 48px; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid var(--border); z-index: 50; overflow: hidden;">
                <a href="profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px;">⚙️ Account Settings</a>
                <a href="logout.php" style="color: #ef4444; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-top: 1px solid #f1f5f9;">🚪 Sign Out</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <h1>Academic Profile Mapping</h1>
        <div class="subtitle">Assign newly approved students to their designated degree streams and batches.</div>

        <?php echo $message; ?>

        <div class="card" style="border-top: 4px solid var(--primary);">
            <div class="card-header">Target Student Allocation</div>
            <form action="admin_assign_student.php" method="POST">
                
                <label for="user_id">Select Approved Student</label>
                <select name="user_id" id="user_id" required>
                    <option value="">-- Choose Student Account --</option>
                    <?php while($s = $students->fetch_assoc()): ?>
                        <option value="<?php echo $s['id']; ?>">
                            <?php echo htmlspecialchars($s['username']); ?> (<?php echo htmlspecialchars($s['email']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <label for="course_code">Assign Course Stream</label>
                <select name="course_code" id="course_code" required>
                    <option value="">-- Choose Course Stream --</option>
                    <?php while($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $c['course_code']; ?>">
                            [<?php echo $c['course_code']; ?>] <?php echo $c['course_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <label for="batch_id">Assign Graduation Batch</label>
                <select name="batch_id" id="batch_id" required>
                    <option value="">-- Choose Graduation Batch --</option>
                    <?php while($b = $batches->fetch_assoc()): ?>
                        <option value="<?php echo $b['id']; ?>">
                            Batch <?php echo $b['batch_name']; ?> (Current Sem: <?php echo $b['current_semester']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <button type="submit" name="update_student">Save Allocation Matrix</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>