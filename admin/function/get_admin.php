<?php
include '..\..\connect.php';

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'];
$sql = "SELECT * FROM admin WHERE uname_admin = '" . $id . "'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        $message = "  <form>
                        <table style=\"width: 100%;\">
                            <tr>
                                <td>Username :</td>
                                <td><input type=\"text\" value=\"" . $row["uname_admin"] . "\" readonly></td>
                                <td style=\"text-align: right;\"><input type=\"button\" value=\"Save Change\" onclick=\"save('" . $row["uname_admin"] . "')\" class=\"button_save\"></td>
                            </tr>
                            <tr>
                                <td>Password:</td>
                                <td><input type=\"password\" name=\"edit_password_" . $row["uname_admin"] . "\" ></td>
                                <td style=\"text-align: right;\"><input type=\"button\" value=\"Delete\" class=\"button_delete\" onclick=\"delete_('" . $row["uname_admin"] . "')\"></td>
                            </tr>
                            <tr>
                                <td>Confirm Password :</td>
                                <td><input type=\"password\" name=\"edit_c_password_" . $row["uname_admin"] . "\"></td>
                                <td style=\"text-align: right;\"><input type=\"button\" value=\"Cancel\" onclick=\"cancel('" . $row["uname_admin"] . "')\" class=\"button_cancel\"></td>
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
