<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['student_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    // Check if student already registered
    $check_query = "SELECT * FROM event_registrations WHERE student_id = ? AND event_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $student_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Already registered
        $_SESSION['message'] = "Anda telah mendaftar untuk acara ini.";
        $_SESSION['message_type'] = "warning";
    } else {
        // Register student
        $insert_query = "INSERT INTO event_registrations (student_id, event_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $student_id, $event_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Pendaftaran berjaya!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Ralat semasa mendaftar. Sila cuba lagi.";
            $_SESSION['message_type'] = "error";
        }
    }
} else {
    $_SESSION['message'] = "Acara tidak sah.";
    $_SESSION['message_type'] = "error";
}

// Redirect back to student dashboard or event list
header("Location: student/student_dashboard.php");
exit();
