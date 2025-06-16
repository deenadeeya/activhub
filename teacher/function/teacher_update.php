<?php
header('Content-Type: application/json');
require_once '../../connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$response = ['status' => 0, 'error' => ''];

try {
    if (!$input) {
        throw new Exception('Data tidak sah');
    }

    // Validate required fields
    if (empty($input['id']) || empty($input['name']) || empty($input['teacher_uname'])) {
        throw new Exception('Nama, username dan ID guru diperlukan');
    }

    $id = $input['id'];
    $name = $input['name'];
    $teacher_uname = $input['teacher_uname'];
    $class_id = $input['class'] ?? null;
    $password = $input['password'] ?? null;

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Verify teacher exists
        $teacher_check = "SELECT teacher_ic FROM teacher WHERE teacher_ic = ?";
        $stmt = $conn->prepare($teacher_check);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $teacher_result = $stmt->get_result();

        if ($teacher_result->num_rows === 0) {
            throw new Exception('Guru tidak dijumpai dalam sistem');
        }

        // 2. Update teacher basic info
        $update_fields = ["teacher_fname = ?", "teacher_uname = ?"];
        $params = [$name, $uname];
        $types = "ss";

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_fields[] = "teacher_pass = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        $update_sql = "UPDATE teacher SET " . implode(', ', $update_fields) . " WHERE teacher_ic = ?";
        $params[] = $id;
        $types .= "s";

        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            throw new Exception('Gagal kemaskini maklumat guru: ' . $conn->error);
        }

        // 3. Handle class assignment
        // First remove from any current class
        $clear_sql = "UPDATE class SET head_teacher = NULL WHERE head_teacher = ?";
        $stmt = $conn->prepare($clear_sql);
        $stmt->bind_param("s", $id);
        if (!$stmt->execute()) {
            throw new Exception('Gagal memadam penugasan kelas sedia ada: ' . $conn->error);
        }

        // If a new class was selected
        if ($class_id) {
            // Verify the class exists
            $class_check = "SELECT class_id FROM class WHERE class_id = ?";
            $stmt = $conn->prepare($class_check);
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $class_result = $stmt->get_result();

            if ($class_result->num_rows === 0) {
                throw new Exception('Kelas yang dipilih tidak dijumpai');
            }

            // Assign teacher to new class
            $assign_sql = "UPDATE class SET head_teacher = ? WHERE class_id = ?";
            $stmt = $conn->prepare($assign_sql);
            $stmt->bind_param("si", $id, $class_id);
            if (!$stmt->execute()) {
                throw new Exception('Gagal menugaskan guru ke kelas: ' . $conn->error);
            }
        }

        // Commit transaction if all queries succeeded
        mysqli_commit($conn);
        $response['status'] = 1;
        $response['message'] = 'Maklumat guru berjaya dikemaskini';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
