<?php

//This file decides what appears in the header based on user role
$teacher = null;
$student = null;

$user_role = $_SESSION['user_role'];

// Fetch teacher data if logged in as teacher
if (
    isset($_SESSION['user_role'], $_SESSION['user_ic']) &&
    $_SESSION['user_role'] === 'teacher'
) {
    $teacher_ic = $_SESSION['user_ic'];
    $teacher_query = mysqli_query($conn, "SELECT teacher_fname FROM teacher WHERE teacher_ic='$teacher_ic'");
    $teacher = mysqli_fetch_assoc($teacher_query);
}

// Fetch student data if logged in as student
if (
    isset($_SESSION['user_role'], $_SESSION['user_ic']) &&
    $_SESSION['user_role'] === 'student'
) {
    $student_ic = $_SESSION['user_ic'];
    $student_query = mysqli_query($conn, "SELECT student_fname FROM student WHERE student_ic='$student_ic'");
    $student = mysqli_fetch_assoc($student_query);
}
