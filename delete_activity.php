<?php
session_start();
include 'connect.php';


// Only allow teachers
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $activity_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM cocu_activities WHERE id = ?");
    $stmt->bind_param("i", $activity_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: viewstudentCocurricular.php");
exit;
?>