<?php
session_start();
require_once '../connect.php';
include '../header.php';

if (!isset($_SESSION['user_role']) || !isset($_SESSION['user_ic'])) {
    header("Location: ../login.php");
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
    $teacher_ic = $_POST['teacher_ic'];
    $teacher_uname = $_POST['teacher_uname'];
    $password = $_POST['teacher_pass'];
    $teacher_pass = password_hash($password, PASSWORD_DEFAULT);
    $teacher_fname = $_POST['teacher_fname'];
    $teacher_contact = $_POST['teacher_contact'];
    $teacher_email = $_POST['teacher_email'];
    $teacher_dob = $_POST['teacher_dob'];
    $teacher_doe = $_POST['teacher_doe'];
    $teacher_address = $_POST['teacher_address'];
    $class_id = !empty($_POST['class']) ? $_POST['class'] : null;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert teacher (without class assignment)
        $stmt = $conn->prepare("INSERT INTO teacher (teacher_ic, teacher_uname, teacher_pass, teacher_fname, teacher_contact, teacher_email, teacher_dob, teacher_doe, teacher_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssss",
            $teacher_ic,
            $teacher_uname,
            $teacher_pass,
            $teacher_fname,
            $teacher_contact,
            $teacher_email,
            $teacher_dob,
            $teacher_doe,
            $teacher_address
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to add teacher: " . $conn->error);
        }

        // If a class was selected, assign as head teacher
        if ($class_id) {
            $update_sql = "UPDATE class SET head_teacher = ? WHERE class_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $teacher_ic, $class_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to assign class: " . $conn->error);
            }
        }

        // Commit transaction
        $conn->commit();
        echo "<script>alert('Guru berjaya didaftarkan'); window.location.href='admin_add_teacher.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Gagal mendaftar guru: " . addslashes($e->getMessage()) . "');</script>";
    }
}

$class_query = $conn->query("SELECT * FROM class");
$classes = $class_query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Tambah Guru - SRIAAWP ActivHub</title>
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
                <span class="admin-text"><?= strtoupper($user_role) ?></span><br>
                <span class="welcome-text">Selamat Datang, <?= htmlspecialchars($username) ?>!</span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <h1 class="profile-title">TAMBAH AKAUN BARU GURU</h1>

        <form method="POST">
            <section class="left-card">
                <div class="profile-header">
                    <img src="../img/profile.jpg" alt="Student Image" class="profile-pic">
                    <h2>Daftar Guru Baru</h2>
                </div>

                <div class="info-group">
                    <label>Nombor IC:</label>
                    <input type="text" name="teacher_ic" required pattern="\d{12}" title="12 digit nombor IC tanpa '-'">

                    <label>Kata Laluan:</label>
                    <input type="password" name="teacher_pass" required minlength="8">

                    <label>Nama Penuh:</label>
                    <input type="text" name="teacher_fname" required>

                    <label>Nama Pengguna/Username:</label>
                    <input type="text" name="teacher_uname" required>

                    <label>Nombor Telefon:</label>
                    <input type="tel" name="teacher_contact" required pattern="[0-9]{10,11}" title="10 atau 11 digit nombor telefon">

                    <label>Emel:</label>
                    <input type="email" name="teacher_email">

                    <label>Tarikh Lahir:</label>
                    <input type="date" name="teacher_dob">

                    <label>Tarikh Mula Bekerja:</label>
                    <input type="date" name="teacher_doe">

                    <label>Alamat:</label>
                    <textarea name="teacher_address"></textarea>

                    <label class="no-asterisk">Kelas (Guru Kelas):</labelc>
                        <select name=" class">
                            <option value="">-- Tidak Ditugaskan --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                </div>

                <div class="action-buttons">
                    <button class="yellow" type="submit">SIMPAN</button>
                    <button class="red" type="button" onclick="location.href='../teacher/teacherList.php'">BATAL</button>
                </div>
            </section>
        </form>
    </div>
</body>

</html>