<?php
include 'db_connect.php';
session_start();

// 1. Security Gate Check (Move this to the absolute top!)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = ""; // Initialize message variable ONCE right under security

// Now run your counts and form logic below it safely...
$count_students = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student' AND status='approved'")->fetch_assoc()['total'];

// Fetch system analytics counts for the UI metrics cards
$count_students = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student' AND status='approved'")->fetch_assoc()['total'];
$count_faculty = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='faculty' AND status='approved'")->fetch_assoc()['total'];
$count_pending = $conn->query("SELECT COUNT(*) as total FROM users WHERE status='pending'")->fetch_assoc()['total'];

// Handle Notice/Announcement Submission
if (isset($_POST['submit_notice'])) {
    $title = mysqli_real_escape_string($conn, $_POST['notice_title']);
    $message_text = mysqli_real_escape_string($conn, $_POST['notice_message']);
    $admin_user = $_SESSION['username'];

    $notice_sql = "INSERT INTO announcements (title, message, created_by) VALUES ('$title', '$message_text', '$admin_user')";
    
    if ($conn->query($notice_sql) === TRUE) {
        $message = "<div class='success'>Notice published successfully to all dashboards!</div>";
    } else {
        $message = "<div class='error'>Error publishing notice: " . $conn->error . "</div>";
    }
}

// 2. Handle Fee Assignment / Update
if (isset($_POST['submit_fee'])) {
    $student_id = intval($_POST['student_id']);
    $total_amount = floatval($_POST['total_amount']);
    $amount_paid = floatval($_POST['amount_paid']);

    if ($amount_paid >= $total_amount) {
        $fee_status = 'Paid';
    } elseif ($amount_paid > 0) {
        $fee_status = 'Partially Paid';
    } else {
        $fee_status = 'Pending';
    }

    $check_fee = "SELECT * FROM fees WHERE student_id = $student_id";
    $fee_exists = $conn->query($check_fee);

    if ($fee_exists->num_rows > 0) {
        $fee_sql = "UPDATE fees SET total_amount = $total_amount, amount_paid = $amount_paid, status = '$fee_status' WHERE student_id = $student_id";
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

// 3. Handle Approval / Rejection Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $update_sql = "UPDATE users SET status='approved' WHERE id=$user_id";
        if ($conn->query($update_sql) === TRUE) {
            $message = "<div class='success'>User account approved successfully!</div>";
        }
    } elseif ($action === 'reject') {
        $delete_sql = "DELETE FROM users WHERE id=$user_id";
        if ($conn->query($delete_sql) === TRUE) {
            $message = "<div class='error'>User registration request rejected and removed.</div>";
        }
    } elseif ($action === 'remove') {
        // Remove an active approved member
        $delete_sql = "DELETE FROM users WHERE id=$user_id";
        if ($conn->query($delete_sql) === TRUE) {
            $message = "<div class='error'>Member account removed permanently from the system.</div>";
        } else {
            $message = "<div class='error'>Error removing member: " . $conn->error . "</div>";
        }
    }
}

// 4. Fetch Queries for Layout
$pending_sql = "SELECT id, username, email, role FROM users WHERE status='pending'";
$pending_result = $conn->query($pending_sql);

$students_sql = "SELECT id, username FROM users WHERE role='student' AND status='approved'";
$students_result = $conn->query($students_sql);

// Fetch all active approved members (both students and faculty)
$active_users_sql = "SELECT id, username, email, role FROM users WHERE status='approved' ORDER BY role, username ASC";
$active_users_result = $conn->query($active_users_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS - Admin Dashboard</title>
    
    <style>
    :root {
        --primary: #4f46e5;
        --success: #10b981;
        --danger: #ef4444;
        --bg: #f8fafc;
        --card-bg: #ffffff;
        --text: #0f172a;
        --border: #e2e8f0;
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; display: flex; min-height: 100vh; }
    
    /* Layout Structure */
    .sidebar { width: 260px; background: #1e293b; color: white; padding: 25px 20px; box-sizing: border-box; }
    .sidebar h3 { margin: 0 0 30px 0; font-size: 20px; letter-spacing: 0.5px; color: #38bdf8; }
    .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; font-weight: 500; transition: all 0.2s; }
    .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
    .sidebar a.logout-btn { background: #rgba(239, 68, 68, 0.1); color: #f87171; margin-top: 40px; }
    .sidebar a.logout-btn:hover { background: var(--danger); color: white; }
    
    .main-content { flex: 1; padding: 40px; box-sizing: border-box; overflow-y: auto; }
    .welcome-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
    .welcome-header h1 { margin: 0; font-size: 28px; font-weight: 700; color: var(--text); }
    
    /* Grid Analytics Cards */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 35px; }
    .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; }
    .stat-card .label { font-size: 14px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-card .value { font-size: 32px; font-weight: 700; margin: 10px 0 0 0; color: #1e293b; }
    
    /* Content Panels */
    .panel { background: var(--card-bg); padding: 30px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 35px; }
    .panel h2 { margin-top: 0; font-size: 20px; font-weight: 600; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border); }
    
    /* Tables and Forms */
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 14px 16px; background: #f1f5f9; font-weight: 600; color: #475569; font-size: 14px; }
    td { padding: 14px 16px; border-bottom: 1px solid var(--border); font-size: 15px; }
    .btn { padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; display: inline-block; transition: background 0.2s; }
    .btn-approve { background: #d1fae5; color: #065f46; margin-right: 8px; }
    .btn-approve:hover { background: #bbf7d0; }
    .btn-reject { background: #fee2e2; color: #991b1b; }
    .btn-reject:hover { background: #fecaca; }
    
    input, select, button { width: 100%; padding: 10px 14px; margin-top: 8px; margin-bottom: 15px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 15px; }
    button { background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
    button:hover { background: #4338ca; }
    .success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
    .error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
</style>

</head>
<body>

<!-- <div class="sidebar">
    <h3>SECMS Admin</h3>
    <a href="admin_dashboard.php" class="active">Dashboard Workspace</a>
    <a href="profile.php">My Account Profile</a>
    <a href="logout.php" class="logout-btn">Sign Out System</a>
</div> -->

<div class="sidebar">
    <h3>SECMS Admin</h3>
    <a href="admin_dashboard.php" class="active">Dashboard Workspace</a>
    <a href="manage_academic_config.php">Academic Config</a> <a href="profile.php">My Account Profile</a>
    <a href="admin_assign_student.php">Assign Student Profiles</a>
    <a href="logout.php" class="logout-btn">Sign Out System</a>
</div>

<div class="main-content">
    <div class="welcome-header">
        <h1>Overview Dashboard Workspace</h1>
        <span style="color: #64748b; font-weight: 500;">Logged in: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
    </div>

    <?php if (!empty($message)) echo $message; ?>

    <div class="stats-grid">
        <div class="stat-card" style="border-top: 4px solid var(--primary);">
            <span class="label">Approved Students Set</span>
            <span class="value"><?php echo $count_students; ?></span>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--success);">
            <span class="label">Approved Faculty Staff</span>
            <span class="value"><?php echo $count_faculty; ?></span>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--danger);">
            <span class="label">Pending Requests Queue</span>
            <span class="value"><?php echo $count_pending; ?></span>
        </div>
    </div>

    <div class="panel">
        <h2>System Registration Requests Gate</h2>
        <table>
            <thead>
                <tr>
                    <th>Username Identifier</th>
                    <th>Email Address Contacts</th>
                    <th>Requested System Role</th>
                    <th>Action Controller Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                    <?php while($row = $pending_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-weight:600; font-size:13px;"><?php echo ucfirst($row['role']); ?></span></td>
                            <td>
                                <a href="admin_dashboard.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-approve">Grant Access</a>
                                <a href="admin_dashboard.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-reject" onclick="return confirm('Drop registration file entry permanently?')">Drop Entry</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">Registration queue empty. No actions pending.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>Manage Active Campus Members</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email Contact</th>
                    <th>System Role</th>
                    <th>Action Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($active_users_result && $active_users_result->num_rows > 0): ?>
                    <?php while($member = $active_users_result->fetch_assoc()): ?>
                        <?php if ($member['id'] == $_SESSION['user_id']) continue; ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($member['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td>
                                <span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight:600; font-size:13px;">
                                    <?php echo ucfirst($member['role']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin_dashboard.php?action=remove&id=<?php echo $member['id']; ?>" class="btn btn-reject" onclick="return confirm('Are you completely sure you want to permanently revoke access and delete this member?')">Remove Member</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">No active members found in the directory.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel" style="max-width: 600px;">
        <h2>Financial Accounts & Fees Ledger Config</h2>
        <form action="admin_dashboard.php" method="POST">
            <label for="fee_student">Select Target Student Profile:</label>
            <select name="student_id" id="fee_student" required>
                <?php if ($students_result && $students_result->num_rows > 0): ?>
                    <?php 
                    $students_result->data_seek(0);
                    while($st = $students_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No authorized student directories loaded</option>
                <?php endif; ?>
            </select>

            <label for="total_amount">Assigned Term Fee Bill Total (₹):</label>
            <input type="number" step="0.01" name="total_amount" id="total_amount" placeholder="e.g. 65000" required>

            <label for="amount_paid">Amount Cleared to Date Balance (₹):</label>
            <input type="number" step="0.01" name="amount_paid" id="amount_paid" placeholder="e.g. 30000" value="0" required>

            <button type="submit" name="submit_fee">Commit Transaction Changes</button>
        </form>
    </div>

    <div class="panel" style="max-width: 600px;">
        <h2>Publish Campus Announcement</h2>
        <form action="admin_dashboard.php" method="POST">
            <label for="notice_title">Notice Heading/Title:</label>
            <input type="text" name="notice_title" id="notice_title" placeholder="e.g., End Semester Examination Schedule" required>

            <label for="notice_message">Detailed Message Content:</label>
            <textarea name="notice_message" id="notice_message" rows="4" style="width: 100%; padding: 10px; margin-top: 8px; margin-bottom: 15px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 15px;" placeholder="Type the complete notice details here..." required></textarea>

            <button type="submit" name="submit_notice">Broadcast Notice Bulletin</button>
        </form>
    </div>

    <div class="panel" style="max-width: 600px;">
        <h2>Class Timetable Schedule Config</h2>
        <form action="admin_dashboard.php" method="POST">
            <label for="day_of_week">Select Day:</label>
            <select name="day_of_week" id="day_of_week" required>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
            </select>

            <label for="time_slot">Time Slot Window:</label>
            <input type="text" name="time_slot" id="time_slot" placeholder="e.g., 10:00 AM - 11:30 AM" required>

            <label for="subject_name">Subject Assignment:</label>
            <input type="text" name="subject_name" id="subject_name" placeholder="e.g., Data Science Basics" required>

            <label for="room_number">Classroom / Lab Location:</label>
            <input type="text" name="room_number" id="room_number" placeholder="e.g., Lab Block B-302" required>

            <button type="submit" name="submit_timetable">Publish Schedule Slot</button>
        </form>
    </div>

</div>

</body>
</html>