<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? 0;

$sql = "SELECT * FROM student 
        INNER JOIN class ON class.class_id = student.student_class 
        WHERE student.student_ic = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $message = "
        <p><strong>{$row["student_fname"]}</strong><br>
        <strong>Class:</strong> {$row["class_name"]}<br>
        <span class=\"credentials\">IC Number:</span> {$row["student_ic"]}<br>
        <button class=\"edit-button\" onclick=\"edit({$row['student_ic']})\">Edit</button>";
} else {
    $message = "Student not found.";
}

$response = ['message' => $message];
header('Content-Type: application/json');
echo json_encode($response);