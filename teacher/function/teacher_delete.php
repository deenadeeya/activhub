<?php
header('Content-Type: application/json');
require_once '../../connect.php';

$response = ['status' => 0, 'error' => ''];

try {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['id'])) {
        throw new Exception("No.Kad Pengenalan tidak sah.");
    }

    $teacher_ic = $input['id'];

    //Check if teacher exists
    $check = $conn->prepare("SELECT * FROM teacher WHERE teacher_ic = ?");
    $check->bind_param("s", $teacher_ic);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Rekod Guru tidak dijumpai.");
    }

    //Clear teacher from any assigned class
    $clear = $conn->prepare("UPDATE class SET head_teacher = NULL WHERE head_teacher = ?");
    $clear->bind_param("s", $teacher_ic);
    $clear->execute();

    //Delete teacher
    $delete = $conn->prepare("DELETE FROM teacher WHERE teacher_ic = ?");
    $delete->bind_param("s", $teacher_ic);
    if ($delete->execute()) {
        $response['status'] = 1;
    } else {
        throw new Exception("Gagal memadam rekod guru.");
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
