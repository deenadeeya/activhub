<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'];
$sql = "SELECT * FROM class WHERE class_id = '" . $id . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        $message = "  <form>
                        <table style=\"width: 100%;\">
                            <tr>
                                <td>Class ID :</td>
                                <td><input type=\"text\" value=\"" . $row["class_id"] . "\" readonly></td>
                                 <td style=\"text-align: right;\"><input type=\"button\" value=\"Save Change\" onclick=\"save('" . $row["class_id"] . "')\" class=\"button_save\"></td>
                            </tr>
                            <tr>
                                <td>Class Name:</td>
                                <td><input type=\"text\" name=\"edit_name_" . $row["class_name"] . "\" ></td>
                                <td style=\"text-align: right;\"><input type=\"button\" value=\"Cancel\" onclick=\"cancel('" . $row["class_id"] . "')\" class=\"button_cancel\"></td>
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
