<?php
include 'db_connect.php';
session_start();

// Security Gate Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$admin_user = $_SESSION['username'] ?? 'Admin';

// Handle Notice/Announcement Submission
if (isset($_POST['submit_notice'])) {
    $title = mysqli_real_escape_string($conn, $_POST['notice_title']);
    $message_text = mysqli_real_escape_string($conn, $_POST['notice_message']);
    
    $notice_sql = "INSERT INTO announcements (title, message, created_by) VALUES ('$title', '$message_text', '$admin_user')";
    if ($conn->query($notice_sql) === TRUE) {
        $message = "<div class='success'>Notice published successfully to all dashboards!</div>";
    } else {
        $message = "<div class='error'>Error publishing notice: " . $conn->error . "</div>";
    }
}

// Handle Fee Assignment / Update
if (isset($_POST['submit_fee'])) {
    $student_id = intval($_POST['student_id']);
    $total_amount = floatval($_POST['total_amount']);
    $amount_paid = floatval($_POST['amount_paid']);
    
    $fee_status = ($amount_paid >= $total_amount) ? 'Paid' : (($amount_paid > 0) ? 'Partially Paid' : 'Pending');
    
    $check_fee = $conn->query("SELECT * FROM fees WHERE student_id = $student_id");
    if ($check_fee->num_rows > 0) {
        $fee_sql = "UPDATE fees SET total_amount = $total_amount, amount_paid = $amount_paid, status = '$fee_status', updated_at = NOW() WHERE student_id = $student_id";
    } else {
        $fee_sql = "INSERT INTO fees (student_id, total_amount, amount_paid, status) VALUES ($student_id, $total_amount, $amount_paid, '$fee_status')";
    }
    
    if ($conn->query($fee_sql) === TRUE) {
        $message = "<div class='success'>Fee structure updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error updating fees: " . $conn->error . "</div>";
    }
}

// Handle Timetable Slot Submission
if (isset($_POST['submit_timetable'])) {
    $day_of_week = mysqli_real_escape_string($conn, $_POST['day_of_week']);
    $time_slot = mysqli_real_escape_string($conn, $_POST['time_slot']);
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    $time_sql = "INSERT INTO timetables (day_of_week, time_slot, subject_name, room_number) VALUES ('$day_of_week', '$time_slot', '$subject_name', '$room_number')";
    if ($conn->query($time_sql) === TRUE) {
        $message = "<div class='success'>Timetable slot added successfully!</div>";
    } else {
        $message = "<div class='error'>Error adding slot: " . $conn->error . "</div>";
    }
}

// Handle Approval / Rejection Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        if ($conn->query("UPDATE users SET status='approved' WHERE id=$user_id") === TRUE) {
            $message = "<div class='success'>User account approved successfully!</div>";
        }
    } elseif ($action === 'reject' || $action === 'remove') {
        if ($conn->query("DELETE FROM users WHERE id=$user_id") === TRUE) {
            $message = "<div class='error'>User account removed from the system.</div>";
        }
    }
}

// Fetch Dashboard Data
$count_students = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student' AND status='approved'")->fetch_assoc()['total'];
$count_faculty = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='faculty' AND status='approved'")->fetch_assoc()['total'];
$count_pending = $conn->query("SELECT COUNT(*) as total FROM users WHERE status='pending'")->fetch_assoc()['total'];
$count_courses = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc()['total'];

$pending_result = $conn->query("SELECT id, username, email, role FROM users WHERE status='pending' ORDER BY created_at ASC");
$students_result = $conn->query("SELECT id, username FROM users WHERE role='student' AND status='approved' ORDER BY username ASC");

$finance_data = $conn->query("SELECT SUM(total_amount) as expected, SUM(amount_paid) as collected FROM fees")->fetch_assoc();
$total_expected = $finance_data['expected'] ?? 0;
$total_collected = $finance_data['collected'] ?? 0;
$total_due = $total_expected - $total_collected;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - SECMS</title>
    <style>
        :root {
            --primary: #0f172a; /* Dark Slate for Sidebar */
            --accent: #4f46e5; /* Indigo for Buttons & Active Tabs */
            --accent-hover: #4338ca;
            --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
            --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #1e293b; --text-light: #64748b;
        }
        
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; padding: 25px 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h3 { margin: 0 0 30px 0; color: #818cf8; font-size: 20px; }
        .sidebar a { display: block; color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
        .sidebar a:hover { background: #1e293b; color: white; }
        .sidebar a.active { background: var(--accent); color: white; }
        
        /* Main Workspace */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .top-header { background: var(--card); height: 70px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; }
        
        .main-content { padding: 30px 40px; box-sizing: border-box; overflow-y: auto; }
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        .subtitle { color: var(--text-light); margin-bottom: 30px; font-size: 15px; }
        
        /* CSS Grid Layouts */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 25px; }
        
        /* Cards */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .card-header { margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; font-size: 18px; font-weight: 700; color: var(--text); }
        
        /* Metric Stats */
        .stat-card { background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 12px; }
        .stat-card p { margin: 0; font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--text-light); }
        .stat-card h3 { margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--text); }
        
        /* Tables & Forms */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid var(--border); text-align: left; font-size: 14px; }
        th { color: var(--text-light); font-weight: 600; background: #f8fafc; }
        
        label { font-size: 13px; font-weight: 600; color: var(--text-light); display: block; margin-top: 12px; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px; font-family: inherit; }
        button { background: var(--accent); color: white; border: none; font-weight: 600; cursor: pointer; padding: 12px; border-radius: 6px; width: 100%; margin-top: 15px; transition: 0.2s; }
        button:hover { background: var(--accent-hover); }
        
        /* Buttons / Badges */
        .btn-sm { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block; color: white; }
        .btn-approve { background: var(--success); }
        .btn-reject { background: var(--danger); }
        
        .role-badge { padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #f1f5f9; text-transform: uppercase; }
        
        /* Alerts */
        .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; border: 1px solid #34d399; }
        .error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; border: 1px solid #f87171; }
        
        .col-span-2 { grid-column: span 2; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>SECMS Admin</h3>
    <a href="admin_dashboard.php" class="active">Command Center</a>
    <a href="manage_academic_config.php">Academic Config</a>
    <a href="admin_assign_student.php">Assign Student Profiles</a>
    <a href="admin_profile.php">Profile Sheet</a>
    <a href="profile.php">Account Settings</a>
    <a href="logout.php" style="color: #f87171; margin-top: 40px; display: block;">Sign Out System</a>
</div>

<div class="workspace">
    <header class="top-header">
        <div style="font-size: 15px; font-weight: 500; color: #64748b;">System Role: <strong>Administrator</strong></div>
        
        <div class="profile-menu" style="position: relative; display: inline-block;">
            <div class="profile-trigger" style="display: flex; align-items: center; gap: 10px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; cursor: pointer; font-weight: 600; font-size: 14px; border: 1px solid var(--border);" onclick="var d = document.getElementById('admin-drop'); d.style.display = d.style.display === 'block' ? 'none' : 'block';">
                <div style="width: 28px; height: 28px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                    <?php echo strtoupper(substr($admin_user, 0, 1)); ?>
                </div>
                <?php echo htmlspecialchars($admin_user); ?> ▾
            </div>
            
            <div id="admin-drop" style="display: none; position: absolute; right: 0; top: 48px; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; border: 1px solid var(--border); z-index: 50; overflow: hidden;">
                <a href="admin_profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-bottom: 1px solid #f1f5f9;">👤 Profile Sheet</a>
                <a href="profile.php" style="color: var(--text); padding: 12px 16px; text-decoration: none; display: block; font-size: 14px;">⚙️ Account Settings</a>
                <a href="logout.php" style="color: #ef4444; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; border-top: 1px solid #f1f5f9;">🚪 Sign Out</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <h1>Overview Dashboard Workspace</h1>
        <div class="subtitle">Manage registrations, system financials, and campus broadcasts.</div>

        <?php if (!empty($message)) echo $message; ?>

        <div class="stats-row">
            <div class="stat-card" style="border-top: 4px solid var(--accent);">
                <p>Approved Students</p>
                <h3><?php echo $count_students; ?></h3>
            </div>
            <div class="stat-card" style="border-top: 4px solid var(--success);">
                <p>Faculty Staff</p>
                <h3><?php echo $count_faculty; ?></h3>
            </div>
            <div class="stat-card" style="border-top: 4px solid var(--danger);">
                <p>Pending Approvals</p>
                <h3><?php echo $count_pending; ?></h3>
            </div>
            <div class="stat-card" style="border-top: 4px solid var(--warning);">
                <p>Active Courses</p>
                <h3><?php echo $count_courses; ?></h3>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card col-span-2">
                <div class="card-header">🛡️ Registration Requests Gate</div>
                <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email Address</th>
                                <th>Requested Role</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="role-badge"><?php echo htmlspecialchars($row['role']); ?></span></td>
                                <td style="text-align: right;">
                                    <a href="admin_dashboard.php?action=approve&id=<?php echo $row['id']; ?>" class="btn-sm btn-approve">Approve</a>
                                    <a href="admin_dashboard.php?action=reject&id=<?php echo $row['id']; ?>" class="btn-sm btn-reject" onclick="return confirm('Drop registration request?')">Reject</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px 0; color: var(--text-light);">
                        <div style="font-size: 32px; margin-bottom: 10px;">✅</div>
                        Registration queue is empty. No actions pending.
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">💰 System Financial Ledger</div>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 13px; color: var(--text-light); text-transform: uppercase; font-weight: 600;">Total Expected Revenue</span>
                    <div style="font-size: 24px; font-weight: 700; color: var(--text);">₹<?php echo number_format($total_expected, 2); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 13px; color: var(--text-light); text-transform: uppercase; font-weight: 600;">Total Collected</span>
                    <div style="font-size: 24px; font-weight: 700; color: var(--success);">₹<?php echo number_format($total_collected, 2); ?></div>
                </div>
                <div style="padding-top: 15px; border-top: 1px solid var(--border);">
                    <span style="font-size: 13px; color: var(--text-light); text-transform: uppercase; font-weight: 600;">Pending Dues</span>
                    <div style="font-size: 20px; font-weight: 700; color: var(--danger);">₹<?php echo number_format($total_due, 2); ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">📢 Publish Campus Notice</div>
                <form action="admin_dashboard.php" method="POST">
                    <label>Notice Heading/Title:</label>
                    <input type="text" name="notice_title" placeholder="e.g., End Semester Exams" required>
                    
                    <label>Detailed Message Content:</label>
                    <textarea name="notice_message" rows="3" placeholder="Type the complete notice details here..." required></textarea>
                    
                    <button type="submit" name="submit_notice">Broadcast Notice</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">💳 Assign / Update Fees</div>
                <form action="admin_dashboard.php" method="POST">
                    <label>Select Target Student:</label>
                    <select name="student_id" required>
                        <option value="">-- Select Approved Student --</option>
                        <?php 
                        if ($students_result && $students_result->num_rows > 0) {
                            $students_result->data_seek(0);
                            while($st = $students_result->fetch_assoc()) {
                                echo "<option value='".$st['id']."'>".htmlspecialchars($st['username'])."</option>";
                            }
                        }
                        ?>
                    </select>
                    
                    <label>Total Term Fee (₹):</label>
                    <input type="number" step="0.01" name="total_amount" placeholder="e.g. 65000" required>
                    
                    <label>Amount Cleared (₹):</label>
                    <input type="number" step="0.01" name="amount_paid" value="0" required>
                    
                    <button type="submit" name="submit_fee" style="background: var(--success);">Commit Transaction</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">🕒 Publish Class Schedule</div>
                <form action="admin_dashboard.php" method="POST">
                    <label>Select Day:</label>
                    <select name="day_of_week" required>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                    </select>
                    
                    <label>Time Slot Window:</label>
                    <input type="text" name="time_slot" placeholder="e.g., 10:00 AM - 11:30 AM" required>
                    
                    <label>Subject Assignment:</label>
                    <input type="text" name="subject_name" placeholder="e.g., Data Science Basics" required>
                    
                    <label>Classroom Location:</label>
                    <input type="text" name="room_number" placeholder="e.g., Lab Block B-302" required>
                    
                    <button type="submit" name="submit_timetable" style="background: var(--warning); color: white;">Publish Schedule</button>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>