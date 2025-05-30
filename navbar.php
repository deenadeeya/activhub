<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'];
?>

<nav>
    <ul>
        <li><a href="<?= ($user_role === 'admin') ? 'admin/admin_dashboard.php' : (($user_role === 'teacher') ? 'teacher/teacher_dashboard.php' : 'student_dashboard.php') ?>">Dashboard</a></li>
        <li><a href="<?= ($user_role === 'admin') ? 'admin/admin_list.php' : (($user_role === 'teacher') ? 'teacher/teacher_profile.php' : 'student_profile.php') ?>">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>