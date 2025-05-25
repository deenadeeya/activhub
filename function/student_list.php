<?php
include '../connect.php';
$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'];

$sql = "SELECT * FROM student WHERE student_ic = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $message = "<p><strong>{$row['student_name']}</strong><br>
                IC: {$row['student_ic']}<br>
                <button onclick='edit({$row['student_ic']})'>Edit</button>";
} else {
    $message = "Student not found.";
}

echo json_encode(['message' => $message]);