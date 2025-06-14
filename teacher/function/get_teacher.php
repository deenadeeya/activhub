<?php
header('Content-Type: application/json');
require_once '../../connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$response = ['status' => 0, 'error' => ''];

try {
    if (!$input || !isset($input['id'])) {
        throw new Exception('Data tidak sah');
    }

    $teacher_id = mysqli_real_escape_string($conn, $input['id']);

    // Get teacher data
    $teacher_query = "SELECT * FROM teacher WHERE teacher_ic = ?";
    $stmt = $conn->prepare($teacher_query);
    $stmt->bind_param("s", $teacher_id);
    $stmt->execute();
    $teacher_result = $stmt->get_result();

    if ($teacher_result->num_rows === 0) {
        throw new Exception('Guru tidak dijumpai');
    }

    $teacher = $teacher_result->fetch_assoc();

    // Get all classes
    $class_query = "SELECT * FROM class ORDER BY class_name";
    $class_result = mysqli_query($conn, $class_query);
    $classes = [];

    while ($class = mysqli_fetch_assoc($class_result)) {
        $classes[] = $class;
    }

    // Get current class assignment
    $current_class_query = "SELECT class_id FROM class WHERE head_teacher = ?";
    $stmt = $conn->prepare($current_class_query);
    $stmt->bind_param("s", $teacher_id);
    $stmt->execute();
    $current_class_result = $stmt->get_result();
    $current_class = $current_class_result->num_rows > 0
        ? $current_class_result->fetch_assoc()['class_id']
        : null;

    // Build the edit form
    $form = '
    <form onsubmit="event.preventDefault(); saveTeacher(\'' . $teacher_id . '\')">
        <table style="width: 100%;">
            <tr>
                <td>NAMA:</td>
                <td><input type="text" name="edit_name_' . $teacher_id . '" value="' . htmlspecialchars($teacher['teacher_fname']) . '" required></td>
            </tr>
            <tr>
                <td>USERNAME:</td>
                <td><input type="text" name="edit_teacher_uname_' . $teacher_id . '" value="' . htmlspecialchars($teacher['teacher_uname']) . '" required></td>
            </tr>
            <tr>
                <td>KELAS:</td>
                <td>
                    <select name="class_' . $teacher_id . '">
                        <option value="">- Tidak Ditugaskan -</option>';

    foreach ($classes as $class) {
        $selected = $class['class_id'] == $current_class ? ' selected' : '';
        $form .= '<option value="' . htmlspecialchars($class['class_id']) . '"' . $selected . '>' . htmlspecialchars($class['class_name']) . '</option>';
    }

    $form .= '
                    </select>
                </td>
                <td><input type="button" value="Delete" class="button_delete" onclick="delete_(\'' . $teacher_id . '\')"></td>
            </tr>
            <tr>
                <td>NO. KAD PENGENALAN:</td>
                <td><input type="text" value="' . htmlspecialchars($teacher['teacher_ic']) . '" readonly></td>
                <td><button type="submit" class="button_save">Simpan</button></td>
            </tr>
            <tr>
                <td>KATA LALUAN BARU:</td>
                <td><input type="password" name="edit_password_' . $teacher_id . '" placeholder="Biarkan kosong jika tidak mahu tukar"></td>
                <td><button type="button" onclick="location.reload()" class="button_cancel">Batal</button></td>
            </tr>
        </table>
    </form>';

    $response = [
        'status' => 1,
        'message' => $form
    ];
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
