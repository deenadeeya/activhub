<?php
// Test file to check cocurricular board
session_start();

// Simulate different user sessions for testing
if (isset($_GET['test_user'])) {
    switch ($_GET['test_user']) {
        case 'student':
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_ic'] = '123456789012'; // Replace with valid student IC
            break;
        case 'teacher':
            $_SESSION['user_role'] = 'teacher';
            $_SESSION['user_ic'] = '890123456789'; // Replace with valid teacher IC
            break;
        case 'admin':
            $_SESSION['user_role'] = 'admin';
            $_SESSION['admin_name'] = 'Test Admin';
            break;
    }
}

// Redirect to cocurricular board
header("Location: cocurricular/cocurricular_board.php");
exit();
?>
