<?php
session_start();
include '../config/connect.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    $response = [
        'status' => 0,
        'message' => "Akses Ditolak. Sila Daftar Masuk Sebagai Guru."
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'];
$name = $input['name'];
$matrix = $input['matrix'];
$gender = $input['gender'];
$password = $input['password'];
$dob = $input['dob'];
$doe = $input['doe'];
$contact_num = $input['contact_num'];

if ($password != "") {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $update = "UPDATE student SET student_fname='$name', student_pass='$hashedPassword',student_dob='$dob',student_doe='$doe',gender='$gender',matrix='$matrix'
    ,contact_num='$contact_num' WHERE student_ic='$id'";
} else {
    $update = "UPDATE student SET student_fname='$name',student_dob='$dob',student_doe='$doe',gender='$gender',matrix='$matrix'
    ,contact_num='$contact_num' WHERE student_ic='$id'";
}

$status = ($conn->query($update) === TRUE) ? 1 : 2;

$sql = "SELECT student.*, class.class_name 
        FROM student 
        INNER JOIN class ON student.student_class = class.class_id 
        WHERE student.student_ic = '$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$message = "<p><strong>{$row['student_fname']}</strong><br>
            <span class='credentials'>Nombor Kad Pengenalan:</span> {$row['student_ic']}<br>
            <span class='credentials'>Nombor Matric:</span> {$row['matrix']}<br><br>
            <button class='edit-button' onclick='edit(\"{$row['student_ic']}\")'>Edit</button>
            <button class='edit-button' style='margin-left:8px;' onclick='window.location.href=\"viewstudentCocurricular.php?student_ic={$row['student_ic']}\"'>View</button>
            </div>";

$response = [
    'status' => $status,
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);
