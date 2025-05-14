<?php

include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? 0;
$sql = "SELECT * FROM teacher INNER JOIN class ON class.class_id = teacher.class WHERE teacher.teacher_ic = '".$id."'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)) {
        $message = "<p><strong> ".$row["teacher_fname"]."</strong><br>
                        <strong>Class:</strong> ".$row["class_name"]."<br>
                        <span class=\"credentials\">Ic Number :</span> ".$row["teacher_ic"]."<br>
                        <button class=\"edit-button\" onclick=\"edit(".$row['teacher_ic'].")\">Edit</button>";
    }
}else{
    $message = "error!!";
}

$response = [
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);