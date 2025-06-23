<?php
include '../../config/connect.php';

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
    <div class=\"student-info\">
        <div class=\"edit-form\">
            <div class=\"form-group\">
                <label><strong>Nama:</strong></label>
                <input type=\"text\" name=\"edit_name_{$row["student_ic"]}\" value=\"{$row["student_fname"]}\" class=\"form-input\" required>
            </div>
            <div class=\"form-group\">
                <label><strong>Kelas:</strong></label>
                <select name=\"class_{$row["student_ic"]}\" class=\"form-input\" required>$options</select>
            </div>
            <div class=\"form-group\">
                <label><strong>Matrik:</strong></label>
                <input type=\"text\" value=\"{$row["matrix"]}\" class=\"form-input\" readonly>
            </div>
            <div class=\"form-group\">
                <label><strong>Nombor Kad Pengenalan:</strong></label>
                <input type=\"text\" value=\"{$row["student_ic"]}\" class=\"form-input\" readonly>
            </div>
            <div class=\"form-group\">
                <label><strong>Password Baru:</strong></label>
                <input type=\"password\" name=\"edit_password_{$row["student_ic"]}\" class=\"form-input\" placeholder=\"Kosongkan jika tidak mahu ubah\">
            </div>
        </div>
    </div>
    <div class=\"student-actions\">
        <button class=\"btn-save\" onclick=\"save({$row["student_ic"]})\">Simpan</button>
        <button class=\"btn-cancel\" onclick=\"cancel({$row["student_ic"]})\">Batal</button>
    </div>
    <style>
        .edit-form .form-group {
            margin-bottom: 15px;
        }
        .edit-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .edit-form .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-save {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 5px;
            width: 100%;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .btn-save:hover { background-color: #218838; }
        .btn-cancel:hover { background-color: #5a6268; }
    </style>";
} else {
    $message = "Student not found.";
}

$response = ['message' => $message];
header('Content-Type: application/json');
echo json_encode($response);