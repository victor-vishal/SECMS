<?php
include 'db_connect.php';
session_start();

// Security Check: Make sure only logged-in Faculty can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. Handle Attendance Submission
if (isset($_POST['submit_attendance'])) {
    $student_id = intval($_POST['student_id']);
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $status = $_POST['status'];

    $att_sql = "INSERT INTO attendance (student_id, date, status, subject_name) VALUES ($student_id, '$date', '$status', '$subject_name')";
    if ($conn->query($att_sql) === TRUE) {
        $message = "<div class='success'>Attendance recorded successfully!</div>";
    } else {
        $message = "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// 2. Handle Marks Submission
if (isset($_POST['submit_marks'])) {
    $student_id = intval($_POST['student_id']);
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $semester = intval($_POST['semester']);
    $marks_obtained = intval($_POST['marks_obtained']);
    $total_marks = intval($_POST['total_marks']);

    $marks_sql = "INSERT INTO academics (student_id, subject_name, marks_obtained, total_marks, semester) VALUES ($student_id, '$subject_name', $marks_obtained, $total_marks, $semester)";
    if ($conn->query($marks_sql) === TRUE) {
        $message = "<div class='success'>Academic marks updated successfully!</div>";
    } else {
        $message = "<div class='error'>Error: " . $conn->error . "</div>";
    }
}

// Fetch all approved students to display in the dropdown menus
$students_sql = "SELECT id, username FROM users WHERE role='student' AND status='approved'";
$students_result = $conn->query($students_sql);
$students = [];
if ($students_result->num_rows > 0) {
    while($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SECMS - Faculty Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .dashboard-container { max-width: 900px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { color: #333; margin-top: 0; border-bottom: 2px solid #17a2b8; padding-bottom: 10px; }
        .welcome-bar { display: flex; justify-content: space-between; align-items: center; background: #17a2b8; color: white; padding: 10px 20px; border-radius: 5px; margin-bottom: 20px; }
        .welcome-bar a { color: white; text-decoration: none; font-weight: bold; background: #dc3545; padding: 5px 10px; border-radius: 4px; }
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .form-card { background: #fdfdfd; padding: 20px; border: 1px solid #e0e0e0; border-radius: 6px; }
        .form-card h3 { margin-top: 0; color: #17a2b8; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        label { display: block; margin-top: 10px; font-weight: bold; font-size: 14px; }
        input, select, button { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #17a2b8; color: white; border: none; cursor: pointer; font-size: 15px; font-weight: bold; margin-top: 15px; }
        button:hover { background: #138496; }
        .success { color: green; font-weight: bold; margin-bottom: 15px; }
        .error { color: red; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="welcome-bar">
        <span>Welcome Faculty, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <a href="logout.php">Logout</a>
    </div>

    <h2>Faculty Management Console</h2>
    <?php echo $message; ?>

    <div class="grid-container">
        <div class="form-card">
            <h3>Mark Student Attendance</h3>
            <form action="faculty_dashboard.php" method="POST">
                <label for="att_student">Select Student:</label>
                <select name="student_id" id="att_student" required>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="att_subject">Subject Name:</label>
                <input type="text" name="subject_name" id="att_subject" placeholder="e.g., Software Engineering" required>

                <label for="att_date">Date:</label>
                <input type="date" name="date" id="att_date" value="<?php echo date('Y-m-d'); ?>" required>

                <label for="att_status">Status:</label>
                <select name="status" id="att_status" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                </select>

                <button type="submit" name="submit_attendance">Save Attendance</button>
            </form>
        </div>

        <div class="form-card">
            <h3>Submit Academic Marks</h3>
            <form action="faculty_dashboard.php" method="POST">
                <label for="marks_student">Select Student:</label>
                <select name="student_id" id="marks_student" required>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="marks_subject">Subject Name:</label>
                <input type="text" name="subject_name" id="marks_subject" placeholder="e.g., Software Engineering" required>

                <label for="semester">Semester:</label>
                <select name="semester" id="semester" required>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                    <option value="3">Semester 3</option>
                    <option value="4">Semester 4</option>
                </select>

                <label for="marks_obtained">Marks Obtained:</label>
                <input type="number" name="marks_obtained" id="marks_obtained" min="0" max="100" placeholder="e.g., 85" required>

                <label for="total_marks">Total Marks:</label>
                <input type="number" name="total_marks" id="total_marks" value="100" required>

                <button type="submit" name="submit_marks">Assign Marks</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>