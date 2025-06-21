<?php
require_once '../includes/session_check.php';
include '../config/connect.php';


// Only allow teachers
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $activity_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM cocu_activities WHERE id = ?");
    $stmt->bind_param("i", $activity_id);
    $stmt->execute();
    $stmt->close();
}

// Build the correct return URL with student_ic parameter
$redirect_url = '../student/viewstudentCocurricular.php';
if (isset($_GET['student_ic'])) {
    $redirect_url .= '?student_ic=' . urlencode($_GET['student_ic']);
}

header("Location: " . $redirect_url);
exit;
?>