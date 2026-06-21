<?php
include 'db_connect.php';
session_start();

// Security check: Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

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
    <title>Admin Panel - Academic Assignment</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f5f7; margin: 0; padding: 40px; display: flex; justify-content: center; }
        .assignment-container { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 450px; border: 1px solid #e2e8f0; }
        h2 { margin-top: 0; color: #1e293b; text-align: center; margin-bottom: 25px; }
        label { font-weight: 600; font-size: 13px; color: #475569; display: block; margin-top: 15px; }
        select, button { width: 100%; padding: 12px; margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { background: #2563eb; color: white; border: none; font-weight: 600; cursor: pointer; margin-top: 25px; }
        button:hover { background: #1d4ed8; }
        .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 15px; font-size: 14px; }
        .error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 15px; font-size: 14px; }
        .back-link { text-align: center; margin-top: 20px; font-size: 14px; }
        .back-link a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>

<div class="assignment-container">
    <h2>Academic Profile Mapping</h2>
    <?php echo $message; ?>

    <form action="admin_assign_student.php" method="POST">
        
        <label for="user_id">Select Approved Student:</label>
        <select name="user_id" id="user_id" required>
            <option value="">-- Choose Student Account --</option>
            <?php while($s = $students->fetch_assoc()): ?>
                <option value="<?php echo $s['id']; ?>">
                    <?php echo htmlspecialchars($s['username']); ?> (<?php echo htmlspecialchars($s['email']); ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label for="course_code">Assign Course Stream:</label>
        <select name="course_code" id="course_code" required>
            <option value="">-- Choose Course Stream --</option>
            <?php while($c = $courses->fetch_assoc()): ?>
                <option value="<?php echo $c['course_code']; ?>">
                    [<?php echo $c['course_code']; ?>] <?php echo $c['course_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="batch_id">Assign Graduation Batch:</label>
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

    <div class="back-link">
        <a href="admin_dashboard.php">← Back to Main Workspace Dashboard</a>
    </div>
</div>

</body>
</html>