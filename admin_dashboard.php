<?php
include 'db_connect.php';
session_start();

// 1. Security Check: Always run this first before handling ANY form data
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = ""; // Initialize message variable ONCE at the top

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
    }
}

// 4. Fetch Queries for Layout
$pending_sql = "SELECT id, username, email, role FROM users WHERE status='pending'";
$pending_result = $conn->query($pending_sql);

$students_sql = "SELECT id, username FROM users WHERE role='student' AND status='approved'";
$students_result = $conn->query($students_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS - Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .dashboard-container { max-width: 900px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { color: #333; border-bottom: 2px solid #007BFF; padding-bottom: 10px; }
        .welcome-bar { display: flex; justify-content: space-between; align-items: center; background: #007BFF; color: white; padding: 10px 20px; border-radius: 5px; margin-bottom: 20px; }
        .welcome-bar a { color: white; text-decoration: none; font-weight: bold; background: #dc3545; padding: 5px 10px; border-radius: 4px; }
        .welcome-bar a:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f1f1f1; color: #333; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold; }
        .btn-approve { background: #28a745; color: white; margin-right: 5px; }
        .btn-approve:hover { background: #218838; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-reject:hover { background: #c82333; }
        .success { color: green; margin-bottom: 15px; font-weight: bold; }
        .error { color: red; margin-bottom: 15px; font-weight: bold; }
        .no-data { text-align: center; color: #777; padding: 20px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- <div class="welcome-bar">
        <span>Welcome, <strong>Admin (<?php echo htmlspecialchars($_SESSION['username']); ?>)</strong></span>
        <a href="logout.php">Logout</a>
    </div> -->

    <div class="welcome-bar">
    <span>Welcome Student, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
    <div>
        <a href="profile.php" style="color: white; text-decoration: none; margin-right: 15px; background: #007BFF; padding: 5px 10px; border-radius: 4px;">My Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

    <h2>Pending Account Approvals</h2>
    <?php echo $message; ?>

    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Requested Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                <?php while($row = $pending_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo ucfirst($row['role']); ?></td>
                        <td>
                            <a href="admin_dashboard.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-approve">Approve</a>
                            <a href="admin_dashboard.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this request?')">Reject</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">No pending registration requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr style="margin: 40px 0; border: 0; border-top: 1px solid #ddd;">

    <h2>Manage Student Fees</h2>
    <div style="background: #fdfdfd; padding: 20px; border: 1px solid #e0e0e0; border-radius: 6px; max-width: 500px;">
        <form action="admin_dashboard.php" method="POST">
            <label style="display:block; margin-top:10px; font-weight:bold;" for="fee_student">Select Student:</label>
            <select name="student_id" id="fee_student" style="width:100%; padding:8px; margin-top:5px;" required>
                <?php if ($students_result && $students_result->num_rows > 0): ?>
                    <?php 
                    $students_result->data_seek(0);
                    while($st = $students_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No approved students found</option>
                <?php endif; ?>
            </select>

            <label style="display:block; margin-top:10px; font-weight:bold;" for="total_amount">Total Fee Amount (₹):</label>
            <input type="number" step="0.01" name="total_amount" id="total_amount" placeholder="e.g., 50000" style="width:100%; padding:8px; margin-top:5px;" required>

            <label style="display:block; margin-top:10px; font-weight:bold;" for="amount_paid">Amount Paid (₹):</label>
            <input type="number" step="0.01" name="amount_paid" id="amount_paid" placeholder="e.g., 25000" style="width:100%; padding:8px; margin-top:5px;" value="0" required>

            <button type="submit" name="submit_fee" style="background:#007BFF; color:white; border:none; padding:10px 15px; margin-top:15px; border-radius:4px; cursor:pointer; font-weight:bold; width:100%;">Assign/Update Fee</button>
        </form>
    </div>
</div>

</body>
</html>