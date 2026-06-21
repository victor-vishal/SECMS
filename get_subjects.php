<?php
include 'db_connect.php';

if (isset($_GET['course_code'])) {
    $course_code = mysqli_real_escape_string($conn, $_GET['course_code']);
    
    $result = $conn->query("SELECT id, subject_code, subject_name, semester FROM subjects WHERE course_code = '$course_code' ORDER BY semester, subject_name ASC");
    
    if ($result->num_rows > 0) {
        echo '<option value="">-- Select Subject --</option>';
        while ($row = $result->fetch_assoc()) {
            echo "<option value='".$row['id']."'>[Sem ".$row['semester']."] ".$row['subject_code']." - ".$row['subject_name']."</option>";
        }
    } else {
        echo '<option value="">No subjects mapped to this course</option>';
    }
}
?>