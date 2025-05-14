<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$sql = "DELETE FROM admin WHERE uname_admin='" . $id . "'";

if ($conn->query($sql) === TRUE) {
    $status = 1;
} else {
    $status = 2;
}

$response = [
    'status' => $status
];


header('Content-Type: application/json');
echo json_encode($response);
