<?php
include '../connect.php';
$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'];
$name = $input['name'];
$password = $input['password'];
$dob = $input['dob'];
$doe = $input['doe'];
$address = $input['address'];
$emergency = $input['emergency'];
$guardian_ic = $input['guardian_ic'];
$guardian_name = $input['guardian_name'];
$relationship = $input['relationship'];
$guardian_address = $input['guardian_address'];
$contact_num = $input['contact_num'];

if ($password != "") {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $update = "UPDATE student SET student_fname='$name', student_pass='$hashedPassword',student_dob='$dob',student_doe='$doe',student_address='$address',student_emergency='$emergency'
    ,guardian_ic='$guardian_ic',guardian_name='$guardian_name',relationship='$relationship',guardian_address='$guardian_address',contact_num='$contact_num' WHERE student_ic='$id'";
} else {
    $update = "UPDATE student SET student_fname='$name',student_dob='$dob',student_doe='$doe',student_address='$address',student_emergency='$emergency'
    ,guardian_ic='$guardian_ic',guardian_name='$guardian_name',relationship='$relationship',guardian_address='$guardian_address',contact_num='$contact_num' WHERE student_ic='$id'";
}

$status = ($conn->query($update) === TRUE) ? 1 : 2;

$sql = "SELECT * FROM student WHERE student_ic = '$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$message = "<p><strong>{$row['student_fname']}</strong><br>
            IC: {$row['student_ic']}<br>
            <button onclick='edit({$row['student_ic']})'>Edit</button>";

$response = [
    'status' => $status,
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);
