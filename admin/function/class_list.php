<?php

include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$sql = "SELECT * FROM class WHERE class_id = '" . $id . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $message = "<p><strong>Class :</strong> " . $row["class_id"] . "<br><br>
                        <button class=\"edit-button\" onclick=\"edit('" . $row['class_id'] . "')\">Edit</button>";
    }
} else {
    $message = "error!!";
}

$response = [
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);
