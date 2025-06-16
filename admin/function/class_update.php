<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$class_year = $input['class_year'];
$class_name = $input['class_name'];
$head_teacher = $input['head_teacher'];

$update = "UPDATE class SET class_year='" . $class_year . "',class_name='" . $class_name . "',head_teacher='" . $head_teacher . "' WHERE class_id='" . $id . "'";

if ($conn->query($update) === TRUE) {
    $status = 1;
} else {
    $status = 2;
}

$sql = "SELECT * FROM class WHERE class_id = '" . $id . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $message = "<p><strong>KELAS ID :</strong> " . $row["class_name"] . "<br><br>
                        <button class=\"edit-button\" onclick=\"edit('" . $row['class_id'] . "')\">Kemas Kini</button>";
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
