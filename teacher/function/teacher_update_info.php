<?php
require_once '../../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $teacher_ic = $_SESSION['user_ic'];


    $teacher_fname = $_POST['teacher_fname'];
    $teacher_email = $_POST['teacher_email'];
    $teacher_contact = $_POST['teacher_contact'];
    $teacher_dob = $_POST['teacher_dob'];
    $teacher_doe = $_POST['teacher_doe'];
    $teacher_address = $_POST['teacher_address'];


    $update_query = "UPDATE teacher 
                     SET teacher_fname = ?, teacher_email = ?, teacher_contact = ?, 
                         teacher_dob = ?, teacher_doe = ?, teacher_address = ?
                     WHERE teacher_ic = ?";


    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssss", $teacher_fname, $teacher_email, $teacher_contact, $teacher_dob, $teacher_doe, $teacher_address, $teacher_ic);


    if ($stmt->execute()) {

        $_SESSION['message'] = "Profile updated successfully!";
        echo "yes";
        header("Location: ../teacher_profile.php");
        exit();
    } else {

        $_SESSION['error'] = "Error updating profile. Please try again.";
        header("Location: ../teacher_profile.php");
        exit();
    }
} else {

    header("Location: ../teacher_profile.php");
    exit();
}
