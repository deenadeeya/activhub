<?php
include '../connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$name = $input['name'] ?? '';
$password = $input['password'] ?? '';
$class = $input['class1'] ?? '';

if($password != ""){
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $update = "UPDATE teacher SET teacher_fname='".$name."',teacher_pass='".$hashedPassword."',class=".$class." WHERE teacher_ic=".$id."";
}else{
    $update = "UPDATE teacher SET teacher_fname='".$name."',class=".$class." WHERE teacher_ic=".$id."";
}

if ($conn->query($update) === TRUE) {
  $status = 1;
} else {
  $status = 2;
}

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
    'status' => $status,
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);