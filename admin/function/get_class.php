<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'];
$sql = "SELECT * FROM class WHERE class_id = '" . $id . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $teacher_ic = $row["head_teacher"];
        $sql_teacher = "SELECT * FROM teacher";
        $result_teacher = mysqli_query($conn, $sql_teacher);
        $teacher = "<select name=\"teacher_" . $row["class_id"] . "\">";
        if (mysqli_num_rows($result_teacher)) {
            while ($teacher_row = mysqli_fetch_assoc($result_teacher)) {
                if ($teacher_row["teacher_ic"] == $teacher_ic) {
                    $teacher .= "<option value=\"" . $teacher_row["teacher_ic"] . "\" selected>" . $teacher_row["teacher_fname"] . "</option>";
                    continue;
                }
                $teacher .= "<option value=\"" . $teacher_row["teacher_ic"] . "\" >" . $teacher_row["teacher_fname"] . "</option>";
            }
        }
        $teacher .= "</select>";
        $message = "  <form>
                        <table style=\"width: 100%;\">
                            <tr>
                                <td>KELAS ID :</td>
                                <td><input type=\"text\" value=\"" . $row["class_id"] . "\" readonly></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>TAHUN KELAS :</td>
                                <td><input type=\"text\" value=\"" . $row["class_year"] . "\" name=\"edit_year_" . $row["class_id"] . "\"></td>
                                <td style=\"text-align: right;\"><input type=\"button\" value=\"Kemas Kini\" onclick=\"save('" . $row["class_id"] . "')\" class=\"button_save\"></td>

                            </tr>
                            <tr>
                                <td>NAMA KELAS:</td>
                                <td><input type=\"text\" name=\"edit_name_" . $row["class_id"] . "\" value=\"" . $row["class_name"] . "\"></td>
                                <td style=\"text-align: right;\"><input type=\"button\" value=\"Delete\" class=\"button_delete\" onclick=\"delete_('" . $row["class_id"] . "')\"></td>
                            </tr>
                            <tr>
                                <td>GURU KELAS:</td>
                                <td>" . $teacher . "</td>
                                <td style=\"text-align: right;\"><input type=\"button\" value=\"Batal\" onclick=\"cancel('" . $row["class_id"] . "')\" class=\"button_cancel\"></td>
                            </tr>

                        </table>
                    </form>";
    }
} else {
    $message = "error!!";
}

$response = [
    'message' => $message
];


header('Content-Type: application/json');
echo json_encode($response);
