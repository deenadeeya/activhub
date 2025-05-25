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
    $username = $_POST['username'];
    $password = $_POST['password'];
    $c_password = $_POST['c_password'];


    $stmt = $conn->prepare("SELECT * FROM admin WHERE uname_admin = '" . $username . "'");
    $stmt->execute();
    $res = $stmt->get_result();
    $admin = $res->fetch_assoc();
    if ($admin["uname_admin"] != null) {
        echo "<script>alert('Username already exits!');window.location.href='admin_add.php';</script>";
    }
    if ($password != $c_password) {
        echo "<script>alert('Password and Confirm Password must be same!');window.location.href='admin_add.php';</script>";
    }

    // Hash password as IC (default)
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admin (uname_admin,pass_admin
    ) VALUES ('" . $username . "','" . $hash . "')");

    if ($stmt->execute()) {
        echo "<script>alert('Admin added successfully'); window.location.href='admin_list.php';</script>";
    } else {
        echo "<script>alert('Failed to add admin');</script>";
    }
}


$class_query = $conn->query("SELECT * FROM class");
$classes = $class_query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Tambah Admin - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
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
        <h1 class="profile-title">TAMBAH ADMIN BARU</h1>

        <form method="POST" class="profile-container">
            <section class="left-card">
                <div class="profile-header">
                    <img src="../img/profile.jpg" alt="Student Image" class="profile-pic">
                    <h2>Admin Baru</h2>
                </div>

                <div class="info-group">
                    <label>NAMA PENGGUNA :</label>
                    <input type="text" name="username" required>
                    <label>Kata Laluan:</label>
                    <input type="password" name="password" required>
                    <label>Sahkan Kata Laluan:</label>
                    <input type="password" name="c_password" required>

                    <div class="action-buttons">
                        <button class="yellow" type="submit">SIMPAN</button>
                        <button class="red" type="reset" onclick="location.href='admin_list.php';">BATAL</button>
                    </div>
            </section>
        </form>
    </div>

</body>

</html>