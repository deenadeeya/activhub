<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? '';
$name = $input['name'] ?? '';
$password = $input['password'] ?? '';
$class = $input['class1'] ?? '';

if ($id && $name && $class) {
    if ($password !== '') {
        $student_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE student SET student_fname = '$name', student_pass = '$student_pass', student_class = $class WHERE student_ic = '$id'";
    } else {
        $sql = "UPDATE student SET student_fname = '$name', student_class = $class WHERE student_ic = '$id'";
    }

    $status = ($conn->query($sql) === TRUE) ? 1 : 2;
} else {
    $status = 0;
}

// fetch updated card
$sql = "SELECT * FROM student INNER JOIN class ON class.class_id = student.student_class WHERE student.student_ic = '$id'";
$result = mysqli_query($conn, $sql);
$message = "Update failed.";

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $message = "
        <p><strong>{$row["student_fname"]}</strong><br>
        <strong>Class:</strong> {$row["class_name"]}<br>
        <span class=\"credentials\">IC Number:</span> {$row["student_ic"]}<br>
        <button class=\"edit-button\" onclick=\"edit({$row['student_ic']})\">Edit</button>";
}

$response = ['status' => $status, 'message' => $message];
header('Content-Type: application/json');
echo json_encode($response);
