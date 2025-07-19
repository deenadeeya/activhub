<?php
header('Content-Type: application/json');
require_once '../../config/connect.php';

$response = ['status' => 0, 'error' => ''];

try {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['id']) || empty($input['id'])) {
        throw new Exception("No.Kad Pengenalan tidak sah.");
    }

    $student_ic = $input['id'];

    // Check if student exists
    $check = $conn->prepare("SELECT * FROM student WHERE student_ic = ?");
    $check->bind_param("s", $student_ic);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Rekod Pelajar tidak dijumpai.");
    }

    // Delete student
    $delete = $conn->prepare("DELETE FROM student WHERE student_ic = ?");
    $delete->bind_param("s", $student_ic);
    if ($delete->execute()) {
        $response['status'] = 1;
    } else {
        throw new Exception("Gagal memadam rekod pelajar.");
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
