
<?php
require_once '../connect.php';

if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    $stmt = $conn->prepare("SELECT teacher_fname FROM teacher WHERE class = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $teacher = $res->fetch_assoc();

    echo json_encode(['name' => $teacher['teacher_fname'] ?? '']);
}
?>
