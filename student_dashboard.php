<?php
include 'db_connect.php';
session_start();


// Security Check: Make sure only logged-in Students can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// 1. Fetch Academic Marks
$marks_sql = "SELECT subject_name, marks_obtained, total_marks, semester FROM academics WHERE student_id = $student_id";
$marks_result = $conn->query($marks_sql);

// 2. Fetch Attendance Summary
$att_sql = "SELECT 
                COUNT(*) as total_classes,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_classes
            FROM attendance WHERE student_id = $student_id";
$att_result = $conn->query($att_sql)->fetch_assoc();

$total_classes = $att_result['total_classes'];
$present_classes = $att_result['present_classes'];
$attendance_percentage = $total_classes > 0 ? round(($present_classes / $total_classes) * 100) : 0; // standard fallback if no data

// Fetch detailed attendance breakdown
$att_det_sql = "SELECT date, subject_name, status FROM attendance WHERE student_id = $student_id ORDER BY date DESC";
$att_det_result = $conn->query($att_det_sql);

// 3. Fetch Fees Status
$fees_sql = "SELECT total_amount, amount_paid, status FROM fees WHERE student_id = $student_id";
$fees_result = $conn->query($fees_sql);
$fee_data = $fees_result->fetch_assoc();

// 4. Fetch Complete Class Schedule Timetable
$timetable_sql = "SELECT day_of_week, time_slot, subject_name, room_number FROM timetables ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), time_slot ASC";
$timetable_result = $conn->query($timetable_sql);

// 5. Fetch Latest Campus Announcements
$notices_sql = "SELECT title, message, created_by, created_at FROM announcements ORDER BY created_at DESC LIMIT 5";
$notices_result = $conn->query($notices_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS - Student Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .dashboard-container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { color: #333; margin-top: 30px; border-bottom: 2px solid #28a745; padding-bottom: 5px; }
        .welcome-bar { display: flex; justify-content: space-between; align-items: center; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; margin-bottom: 20px; }
        .welcome-bar a { color: white; text-decoration: none; font-weight: bold; background: #dc3545; padding: 5px 10px; border-radius: 4px; }
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: #fdfdfd; padding: 15px; border: 1px solid #e0e0e0; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background-color: #f1f1f1; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-paid { background: #28a745; color: white; }
        .badge-pending { background: #dc3545; color: white; }
        .badge-partial { background: #ffc107; color: #333; }
        .no-data { text-align: center; color: #888; padding: 15px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="welcome-bar">
        <span>Welcome Student, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <div>
            <a href="profile.php" style="color: white; text-decoration: none; margin-right: 15px; background: #007BFF; padding: 5px 10px; border-radius: 4px;">My Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h2>Latest Campus Bulletins</h2>
    <div style="margin-bottom: 30px;">
        <?php if ($notices_result && $notices_result->num_rows > 0): ?>
            <?php while($notice = $notices_result->fetch_assoc()): ?>
                <div style="background: #fff8e1; border-left: 5px solid #ffb300; padding: 15px; border-radius: 6px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h4 style="margin: 0 0 5px 0; color: #b78103; font-size: 16px;"><?php echo htmlspecialchars($notice['title']); ?></h4>
                    <p style="margin: 0 0 10px 0; font-size: 14px; color: #4b5563; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($notice['message'])); ?></p>
                    <small style="color: #9ca3af; font-size: 12px;">Posted by: <strong><?php echo htmlspecialchars($notice['created_by']); ?></strong> on <?php echo date('M d, Y h:i A', strtotime($notice['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="background: #f1f5f9; padding: 15px; border-radius: 6px; color: #64748b; text-align: center; font-size: 14px;"> No campus announcements posted at this time.</div>
        <?php endif; ?>
    </div>

    <div class="grid-container">
        <div class="card">
            <h3>Overall Attendance</h3>
            <p style="font-size: 24px; font-weight: bold; color: #28a745; margin: 5px 0;">
                <?php echo $attendance_percentage; ?>%
            </p>
            <p style="margin: 0; color: #666;">Attended <?php echo $present_classes; ?> out of <?php echo $total_classes; ?> classes.</p>
        </div>

        <div class="card">
            <h3>Fee Status Summary</h3>
            <?php if ($fee_data): ?>
                <p style="margin: 5px 0;"><strong>Total Due:</strong> ₹<?php echo number_format($fee_data['total_amount'], 2); ?></p>
                <p style="margin: 5px 0;"><strong>Amount Paid:</strong> ₹<?php echo number_format($fee_data['amount_paid'], 2); ?></p>
                <p style="margin: 5px 0;"><strong>Status:</strong> 
                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '', $fee_data['status'])); ?>">
                        <?php echo $fee_data['status']; ?>
                    </span>
                </p>
            <?php else: ?>
                <p class="no-data" style="margin: 5px 0; text-align: left;">No fee structure assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <h2>Academic Performance</h2>
    <table>
        <thead>
            <tr>
                <th>Subject Name</th>
                <th>Semester</th>
                <th>Marks Obtained</th>
                <th>Total Marks</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($marks_result->num_rows > 0): ?>
                <?php while($row = $marks_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td>Sem <?php echo $row['semester']; ?></td>
                        <td><?php echo $row['marks_obtained']; ?></td>
                        <td><?php echo $row['total_marks']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">No academic grades found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Weekly Class Schedule</h2>
    <table>
        <thead>
            <tr>
                <th>Day</th>
                <th>Time Window</th>
                <th>Subject Name</th>
                <th>Room / Lab Location</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($timetable_result && $timetable_result->num_rows > 0): ?>
                <?php while($time_row = $timetable_result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $time_row['day_of_week']; ?></strong></td>
                        <td><?php echo $time_row['time_slot']; ?></td>
                        <td><?php echo htmlspecialchars($time_row['subject_name']); ?></td>
                        <td><span style="background: #e2e8f0; padding: 3px 8px; border-radius: 4px; font-size: 13px; font-weight:600;"><?php echo htmlspecialchars($time_row['room_number']); ?></span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data" style="text-align:center; padding:20px; color:#94a3b8;">No class schedule slots have been published yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Recent Attendance Logs</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Subject</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($att_det_result->num_rows > 0): ?>
                <?php while($row = $att_det_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><strong><?php echo $row['status']; ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="no-data">No attendance records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>