<?php
session_start();
require_once '../connect.php';
include '../header.php';
// Auto logout after 30 minutes of inactivity
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?expired=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time

// Check if user is logged in and is admin
if (!isset($_SESSION['user_ic']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch teachers for the dropdown
$teachers = [];
$teacher_query = $conn->query("SELECT teacher_ic, teacher_fname FROM teacher ORDER BY teacher_fname");
if ($teacher_query) {
    $teachers = $teacher_query->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name']);
    $class_year = trim($_POST['class_year']);
    $head_teacher = $_POST['head_teacher'];

    if (!empty($class_name) && !empty($class_year) && !empty($head_teacher)) {
        $stmt = $conn->prepare("INSERT INTO class (class_name, class_year, head_teacher) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $class_name, $class_year, $head_teacher);

        if ($stmt->execute()) {
            echo "<script>alert('Rekod Kelas Berjaya Ditambah'); window.location.href='admin_classList.php';</script>";
        } else {
            echo "<script>alert('Rekod Kelas Tidak Berjaya Ditambah');</script>";
        }
    } else {
        echo "<script>alert('Sila Lengkapkan Semua Maklumat');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tambah Kelas - SRIAAWP ActivHub</title>
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
                <span class="welcome-text">Selamat Datang,<br> <?= htmlspecialchars($_SESSION['user_ic']) ?>!</span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <h1 class="profile-title">TAMBAH KELAS BARU</h1>
        <form method="POST" class="profile-container">
            <section class="left-card">
                <div class="info-group">
                    <label>TAHUN KELAS:</label>
                    <input type="text" name="class_year" placeholder="1" required>
                    <label>NAMA KELAS:</label>
                    <input type="text" name="class_name" placeholder="1 Nilam" required>
                    <label>GURU KELAS:</label>
                    <select name="head_teacher">
                        <option value="" required>-- Pilih Guru --</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= htmlspecialchars($teacher['teacher_ic']) ?>">
                                <?= htmlspecialchars($teacher['teacher_fname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="action-buttons">
                    <button class="yellow" type="submit">SIMPAN</button>
                    <button class="red" type="reset" onclick="location.href='admin_classList.php'">BATAL</button>
                </div>
            </section>
        </form>
    </div>

</body>

</html>