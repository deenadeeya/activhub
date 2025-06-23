<?php
include '../../config/connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? 0;

$sql = "SELECT student.*, class.class_name FROM student 
        INNER JOIN class ON class.class_id = student.student_class 
        WHERE student.student_ic = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    // Format gender display
    $gender_display = '';
    switch(strtoupper($row['gender'])) {
        case 'L': $gender_display = 'Lelaki'; break;
        case 'P': $gender_display = 'Perempuan'; break;
        default: $gender_display = $row['gender']; break;
    }
    
    // Determine birth year from IC
    $ic_year = substr($row['student_ic'], 0, 2);
    $birth_year = $ic_year >= 50 ? "19" . $ic_year : "20" . $ic_year;
    
    $message = "
        <div class=\"student-info\">
            <p><strong>" . htmlspecialchars($row['student_fname']) . "</strong></p>
            <p><strong>Kelas:</strong> " . htmlspecialchars($row['class_name']) . "</p>
            <p><strong>Matrik:</strong> " . htmlspecialchars($row['matrix']) . "</p>
            <p><strong>Jantina:</strong> $gender_display</p>
            <p><strong>Tahun Lahir:</strong> $birth_year</p>
            <p><span class=\"credentials\">Nombor Kad Pengenalan:</span> " . htmlspecialchars($row['student_ic']) . "</p>
        </div>
        <div class=\"student-actions\">
            <button class=\"edit-button\" onclick=\"edit(" . $row['student_ic'] . ")\">Edit</button>
        </div>";
} else {
    $message = "Student not found.";
}

$response = ['message' => $message];
header('Content-Type: application/json');
echo json_encode($response);