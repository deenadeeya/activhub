<?php
session_start();
include '../config/connect.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    $response = [
        'message' => "Akses Ditolak. Sila Daftar Masuk Sebagai Guru."
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'];

$sql = "SELECT student.*, class.class_name 
        FROM student 
        INNER JOIN class ON student.student_class = class.class_id 
        WHERE student.student_ic = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $message = "<p><strong>{$row['student_fname']}</strong><br>
                <span class='credentials'>Nombor Kad Pengenalan:</span> {$row['student_ic']}<br>
                <span class='credentials'>Nombor Matric:</span> {$row['matrix']}<br><br>
                <button class='edit-button' onclick='edit(\"{$row['student_ic']}\")'>Edit</button>
                <button class='edit-button' style='margin-left:8px;' onclick='window.location.href=\"viewstudentCocurricular.php?student_ic={$row['student_ic']}\"'>View</button>";
} else {
    $message = "Rekod Pelajar Tidak Dijumpai.";
}

echo json_encode(['message' => $message]);
