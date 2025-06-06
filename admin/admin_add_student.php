<?php
session_start();
require_once '../connect.php';
include '../header.php';
if (!isset($_SESSION['user_role']) || !isset($_SESSION['user_ic'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$user_ic = $_SESSION['user_ic'];


if ($user_role === 'admin') {
    $stmt = $conn->prepare("SELECT uname_admin AS name FROM admin WHERE uname_admin = ?");
    $stmt->bind_param("s", $user_ic);
} else {
    $stmt = $conn->prepare("SELECT teacher_fname AS name FROM teacher WHERE teacher_ic = ?");
    $stmt->bind_param("s", $user_ic);
}
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['name'] ?? 'User';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_ic = $_POST['student_ic'];
    $student_fname = $_POST['student_fname'];
    $student_class = $_POST['student_class'];
    $student_dob = $_POST['student_dob'];
    $student_doe = $_POST['student_doe'];
    $student_address = $_POST['student_address'];
    $student_emergency = $_POST['student_emergency'];

    $guardian_ic = $_POST['guardian_ic'];
    $guardian_name = $_POST['guardian_name'];
    $relationship = $_POST['relationship'];
    $guardian_address = $_POST['guardian_address'];
    $contact_num = $_POST['contact_num'];

    $stmt = $conn->prepare("SELECT teacher_ic FROM teacher WHERE class = ?");
    $stmt->bind_param("i", $student_class);
    $stmt->execute();
    $res = $stmt->get_result();
    $teacher = $res->fetch_assoc();
    $teacher_incharge = $teacher['teacher_ic'] ?? null;

    $student_pass = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO student (student_ic, student_pass, student_fname, student_class, student_dob, student_doe, student_address, student_emergency, guardian_ic, guardian_name, relationship, guardian_address,contact_num, teacher_incharge) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssssssss",
        $student_ic,
        $student_pass,
        $student_fname,
        $student_class,
        $student_dob,
        $student_doe,
        $student_address,
        $student_emergency,
        $guardian_ic,
        $guardian_name,
        $relationship,
        $guardian_address,
        $contact_num,
        $teacher_incharge
    );

    if ($stmt->execute()) {
        echo "<script>alert('Student added successfully'); window.location.href='admin_studentList.php';</script>";
    } else {
        echo "<script>alert('Failed to add student');</script>";
    }
}


$class_query = $conn->query("SELECT * FROM class");
$classes = $class_query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Add Student - ActivHub</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
</head>

<body>

    <header>
        <div class="logo-section">
            <img src="../img/logo.png" alt="Logo" />
            <div class="logo-text">
                <span>SRIAAWP ActivHub</span>
                <?php include '../navlinks.php'; ?>
            </div>
        </div>

        <div class="icon-section">
            <div class="admin-section">
                <span class="admin-text"><?= ucfirst($user_role) ?></span><br>
                <span class="welcome-text">Selamat Datang, <?= htmlspecialchars($username) ?>!</span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <h1 class="profile-title">TAMBAH AKAUN BARU PELAJAR</h1>

        <form method="POST" class="profile-container">
            <section class="left-card">
                <div class="profile-header">
                    <img src="../img/profile.jpg" alt="Student Image" class="profile-pic">
                    <h2>New Student Entry</h2>
                </div>

                <div class="info-group">
                    <label>NAMA PENUH:</label>
                    <input type="text" name="student_fname" required>
                    <label>NOMBOR IC:</label>
                    <input type="text" name="student_ic" required>
                    <label>KATA LALUAN:</label>
                    <input type="password" name="student_pass" required>
                    <label>TARIKH LAHIR:</label>
                    <input type="date" name="student_dob">
                    <label>TARIKH DAFTAR DI SEKOLAH:</label>
                    <input type="date" name="student_doe">
                    <label>ALAMAT:</label>
                    <input type="text" name="student_address">
                    <label>NOMBOR TELEFON PENJAGA:</label>
                    <input type="text" name="student_emergency">

                    <label>KELAS:</label>
                    <select name="student_class" required onchange="getTeacher(this.value)">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>GURU KELAS:</label>
                    <input type="text" id="teacher_name" readonly placeholder="Auto-filled based on class" />
                </div>

                <h3>MAKLUMAT PENJAGA</h3>
                <div class="info-group">
                    <label>NOMBOR IC:</label>
                    <input type="text" name="guardian_ic">
                    <label>NAMA PENUH:</label>
                    <input type="text" name="guardian_name">
                    <label>HUBUNGAN:</label>
                    <input type="text" name="relationship">
                    <label>ALAMAT:</label>
                    <input type="text" name="guardian_address">
                    <label>NOMBOR TELEFON:</label>
                    <input type="text" name="contact_num">
                </div>

                <div class="action-buttons">
                    <button class="yellow" type="submit">SIMPAN</button>
                    <button class="red" type="reset" onclick="location.href='admin_studentList.php'">BATAL</button>
                </div>
            </section>
        </form>
    </div>

    <script>
        function getTeacher(classId) {
            if (!classId) return;

            fetch('function/get_teacher.php?class_id=' + classId)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('teacher_name').value = data.name || 'Not found';
                });
        }
    </script>

</body>

</html>