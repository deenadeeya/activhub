<?php
include '../connect.php';
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    $response = [
        'message' => "Akses Ditolak. Sila Daftar Masuk Sebagai Guru."
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? '';
$teacher_ic = $_SESSION['user_ic'];

// First check if the teacher is a head teacher of any class
$sql_check = "SELECT class_id FROM class WHERE head_teacher = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $teacher_ic);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    $response = [
        'message' => "Akses ditolak. Anda bukan guru kelas."
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$class_data = $result_check->fetch_assoc();
$teacher_class = $class_data['class_id'];

// Get student information only if they belong to the teacher's class
$sql = "SELECT * FROM student WHERE student_class = ? AND student_ic = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $teacher_class, $id);
$stmt->execute();
$result = $stmt->get_result();

$message = "Rekod Pelajar tidak ditemui atau anda tidak mempunyai kebenaran untuk mengakses pelajar ini.";

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $gender = $row['gender'] ?? '';

    // Gender options for dropdown
    $genderOptions = [
        'Lelaki' => 'Lelaki',
        'Perempuan' => 'Perempuan'
    ];

    $genderDropdown = "<select name=\"gender_$id\" required>";
    foreach ($genderOptions as $value => $label) {
        $selected = ($value == strtolower($gender)) ? 'selected' : '';
        $genderDropdown .= "<option value=\"$value\" $selected>$label</option>";
    }
    $genderDropdown .= "</select>";

    $message = "
    <form>
        <table>
            <tr>
                <td>NAMA PENUH:</td>
                <td><input type=\"text\" name=\"edit_name_$id\" value=\"" . htmlspecialchars($row["student_fname"]) . "\" required></td>
                <td></td>
            </tr>
            <tr>
                <td>NO.KAD PENGENALAN:</td>
                <td><input type=\"text\" value=\"" . htmlspecialchars($row["student_ic"]) . "\" readonly></td>
                <td></td>
            </tr>
            <tr>
                <td>NO.MATRIX:</td>
                <td><input type=\"text\" name=\"matrix_$id\" value=\"" . htmlspecialchars($row["matrix"] ?? '') . "\" required></td>
                <td></td>
            </tr>
            <tr>
                <td>JANTINA:</td>
                <td>$genderDropdown</td>
                <td></td>
            </tr>
            <tr>
                <td>TARIKH LAHIR:</td>
                <td><input type=\"date\" name=\"student_dob_$id\" value=\"" . htmlspecialchars($row["student_dob"]) . "\" required></td>
                <td></td>
            </tr>
            <tr>
                <td>TARIKH MASUK:</td>
                <td><input type=\"date\" name=\"student_doe_$id\" value=\"" . htmlspecialchars($row["student_doe"]) . "\" required></td>
                <td></td>
            </tr>
            <tr>
                <td>NO. TELEFON:</td>
                <td><input type=\"text\" name=\"contact_num_$id\" value=\"" . htmlspecialchars($row["contact_num"] ?? '') . "\" required></td>
                <td><button type='button' onclick='save(\"$id\")'>Simpan</button></td>
            </tr>
            <tr>
                <td>KATA LALUAN:</td>
                <td><input type=\"password\" name=\"edit_password_$id\"></td>
                <td><button onclick=\"cancel($id)\" >Batal</button></td>
            </tr>
        </table>
    </form>";
}

$response = [
    'message' => $message
];

header('Content-Type: application/json');
echo json_encode($response);
