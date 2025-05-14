<?php
include '../connect.php';
session_start();
$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'];

$teacher_ic = $_SESSION['user_ic'];
$sql_teacher = "SELECT * FROM teacher INNER JOIN class ON class.class_id = teacher.class WHERE teacher_ic = '$teacher_ic'";
$result_teacher = mysqli_query($conn, $sql_teacher);
$teacher = mysqli_fetch_assoc($result_teacher);
$teacher_class = $teacher['class'];


$sql = "SELECT * FROM student WHERE student_class = '$teacher_class' and student_ic='$id'";
$result = mysqli_query($conn, $sql);
$message = "Student not found or you do not have permission to access this student.";

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $message = "
<form>
    <table>
        <tr>
            <td>Name:</td>
            <td><input type=\"text\" name=\"edit_name_$id\" value=\"" . $row["student_fname"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>IC:</td>
            <td><input type=\"text\" value=\"" . $row["student_ic"] . "\" readonly></td>
            <td></td>
        </tr>
        <tr>
            <td>DATE OF BIRTH:</td>
            <td><input type=\"date\" name=\"student_dob_$id\" value=\"" . $row["student_dob"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>DATE OF ENTRY:</td>
            <td><input type=\"date\" name=\"student_doe_$id\" value=\"" . $row["student_doe"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>ADDRESS:</td>
            <td><input type=\"text\" name=\"student_address_$id\" value=\"" . $row["student_address"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>EMERGENCY CONTACT NUMBER:</td>
            <td><input type=\"text\" name=\"student_emergency_$id\" value=\"" . $row["student_emergency"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <th colspan=\"3\"><br>GUARDIAN INFORMATION<br></th>
        </tr>
        <tr>
            <td>IC NUMBER:</td>
            <td><input type=\"text\" name=\"guardian_ic_$id\" value=\"" . $row["guardian_ic"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>FULL NAME:</td>
            <td><input type=\"text\" name=\"guardian_name_$id\" value=\"" . $row["guardian_name"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>RELATIONSHIP:</td>
            <td><input type=\"text\" name=\"relationship_$id\" value=\"" . $row["relationship"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>ADDRESS:</td>
            <td><input type=\"text\" name=\"guardian_address_$id\" value=\"" . $row["guardian_address"] . "\" required></td>
            <td></td>
        </tr>
        <tr>
            <td>CONTACT NUMBER:</td>
            <td><input type=\"text\" name=\"contact_num_$id\" value=\"" . $row["contact_num"] . "\" required></td>
            <td><button onclick='save($id)'>Save</button></td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input type=\"password\" name=\"edit_password_$id\"></td>
            <td><button onclick=\"cancel($id)\" >Cancel</button></td>
        </tr>
    </table>
</form>";
}

$response = [
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);
