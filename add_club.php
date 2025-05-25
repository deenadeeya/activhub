<?php
include 'connect.php';
session_start();
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupName = $_POST['group_name'];
    $groupType = $_POST['group_type'];
    $groupDescription = $_POST['group_description'];
    $advisorName = $_POST['advisor_name'];
    $advisorIC = $_POST['advisor_ic'];
    $presidentIC = $_POST['president_ic'];
    $vicePresidentIC = $_POST['vice_president_ic'];
    $secretaryIC = $_POST['secretary_ic'];
    $treasurerIC = $_POST['treasurer_ic'];
    $totalMembers = $_POST['total_members'];

    $uploadDir = 'logos/';
    $logoPath = '';
    if (!empty($_FILES['logo']['name'])) {
        $fileName = basename($_FILES['logo']['name']);
        $targetFilePath = $uploadDir . time() . '_' . $fileName;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFilePath)) {
            $logoPath = $targetFilePath;
        }
    }

    $stmt = $conn->prepare("INSERT INTO cocurricular_groups (group_name, group_type, group_description, logo_path, advisor_name, advisor_ic, president_ic, vice_president_ic, secretary_ic, treasurer_ic, total_members) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssi", $groupName, $groupType, $groupDescription, $logoPath, $advisorName, $advisorIC, $presidentIC, $vicePresidentIC, $secretaryIC, $treasurerIC, $totalMembers);

    if ($stmt->execute()) {
        echo "<script>alert('New group added successfully!'); window.location.href='cocurricular_board.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Kelab - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/add_cocuboard.css" />
    <link rel="stylesheet" href="css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
   
</head>

<body>
    <header>
        <div class="logo-section">
        <img src="../img/logo.png" alt="Logo" />
        <div class="logo-text">
            <span>SRIAAWP ActivHub</span>
            <?php include 'navlinks.php'; ?>
        </div>
        </div>
        <div class="icon-section">
        <div class="user-section">
            <?php
            if (isset($_SESSION['user_role'])) {
                if ($_SESSION['user_role'] === 'admin') {
                    echo '<span class="admin-text">' . strtoupper($_SESSION['admin_name'] ?? 'ADMIN') . '</span><br>';
                } elseif ($_SESSION['user_role'] === 'teacher' && !empty($teacher['teacher_fname'])) {
                    echo '<span class="admin-text">' . strtoupper($teacher['teacher_fname']) . '</span><br>';
                } elseif ($_SESSION['user_role'] === 'student' && !empty($student['student_fname'])) {
                    echo '<span class="admin-text">' . strtoupper($student['student_fname']) . '</span><br>';
                }
            }
            ?>
            <span class="welcome-text">Selamat Kembali!</span>
        </div>
        <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <h1 class="profile-title">TAMBAH PAPAN KOKURIKULUM</h1>
        <div class="group-form-container">
            <form method="POST" enctype="multipart/form-data">
                <label>Nama Kumpulan</label>
                <input type="text" name="group_name" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>Kategori Kumpulan</label>
                <div style="margin: 5px 0;">
                    <label><input type="checkbox" name="group_type" value="uniform_bodies" onclick="onlyOne(this)"> Badan Beruniform</label><br>
                    <label><input type="checkbox" name="group_type" value="sports" onclick="onlyOne(this)"> Sukan</label><br>
                    <label><input type="checkbox" name="group_type" value="clubs_associations" onclick="onlyOne(this)"> Kelab</label><br>
                    <label><input type="checkbox" name="group_type" value="others" onclick="onlyOne(this)"> Lain-lain</label>
                </div>

                <label>Maklumat</label>
                <textarea name="group_description" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;"></textarea>

                <label>Nama Penasihat</label>
                <input type="text" name="advisor_name" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>IC Penasihat</label>
                <input type="text" name="advisor_ic" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>IC Presiden</label>
                <input type="text" name="president_ic" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>IC Naib Presiden</label>
                <input type="text" name="vice_president_ic" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>IC Setiausaha</label>
                <input type="text" name="secretary_ic" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>IC Bendahari</label>
                <input type="text" name="treasurer_ic" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>Jumlah Ahli</label>
                <input type="number" name="total_members" required min="1" style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>Muat Naik Logo (Pilihan)</label>
                <input type="file" name="logo" accept="image/*" required style="margin:10px 0;">

                <button type="submit" style="background-color:#28a745; color:white; font-weight:bold; padding:12px 20px; border:none; border-radius:8px; cursor:pointer; width:100%; margin-top:20px;">Add Group</button>
            </form>
        </div>
    </div>

    <script>
        function onlyOne(checkbox) {
            var checkboxes = document.getElementsByName('group_type');
            checkboxes.forEach((item) => {
                if (item !== checkbox) item.checked = false;
            });
        }
    </script>

</body>

</html>