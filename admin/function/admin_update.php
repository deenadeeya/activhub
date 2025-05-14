<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$password = $input['password'] ?? '';


$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$update = "UPDATE admin SET pass_admin='" . $hashedPassword . "' WHERE uname_admin='" . $id . "'";


if ($conn->query($update) === TRUE) {
    $status = 1;
} else {
    $status = 2;
}

$sql = "SELECT * FROM admin WHERE uname_admin = '" . $id . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $message = "<p><strong>Username :</strong> " . $row["uname_admin"] . "<br><br>
                        <button class=\"edit-button\" onclick=\"edit('" . $row['uname_admin'] . "')\">Edit</button>";
    }
} else {
    $message = "error!!";
}

$response = [
    'status' => $status,
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);
