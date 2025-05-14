<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? 0;

$sql = "SELECT * FROM student 
        INNER JOIN class ON class.class_id = student.student_class 
        WHERE student.student_ic = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    $sql_class = "SELECT * FROM class WHERE class_id != " . $row["class_id"];
    $result_class = mysqli_query($conn, $sql_class);

    $options = "<option value=\"" . $row["class_id"] . "\">" . $row["class_name"] . "</option>";
    while ($class = mysqli_fetch_assoc($result_class)) {
        $options .= "<option value=\"" . $class["class_id"] . "\">" . $class["class_name"] . "</option>";
    }

    $message = "
    <form>
        <table style=\"width: 100%;\">
            <tr>
                <td>Name:</td>
                <td><input type=\"text\" name=\"edit_name_{$row["student_ic"]}\" value=\"{$row["student_fname"]}\" required></td>
                <td></td>
            </tr>
            <tr>
                <td>Class:</td>
                <td>
                    <select name=\"class_{$row["student_ic"]}\" required>$options</select>
                </td>
                <td style=\"text-align: right;\">
                    <input type=\"button\" value=\"Save Change\" onclick=\"save({$row["student_ic"]})\" class=\"button_save\">
                </td>
            </tr>
            <tr>
                <td>IC Number:</td>
                <td><input type=\"text\" value=\"{$row["student_ic"]}\" readonly></td>
                <td style=\"text-align: right;\">
                    <input type=\"button\" value=\"Delete\" class=\"button_delete\">
                </td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type=\"password\" name=\"edit_password_{$row["student_ic"]}\"></td>
                <td style=\"text-align: right;\">
                    <input type=\"button\" value=\"Cancel\" onclick=\"cancel({$row["student_ic"]})\" class=\"button_cancel\">
                </td>
            </tr>
        </table>
    </form>";
} else {
    $message = "Student not found.";
}

$response = ['message' => $message];
header('Content-Type: application/json');
echo json_encode($response);