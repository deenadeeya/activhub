<?php
session_start();
include 'connect.php';
include 'header.php';

if (!isset($_GET['group'])) {
    echo "No group selected.";
    exit();
}

$originalGroupName = urldecode($_GET['group']);
$sql = "SELECT * FROM cocurricular_groups WHERE group_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $originalGroupName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Group not found.";
    exit();
}

$group = $result->fetch_assoc();

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

    $logoPath = $group['logo_path'];

    if (!empty($_FILES['logo']['name'])) {
        $uploadDir = 'logos/';
        $fileName = basename($_FILES['logo']['name']);
        $newLogoPath = $uploadDir . time() . '_' . $fileName;

        // Try to upload the new file
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $newLogoPath)) {
            // Delete old file if it exists and is different from the default or current file
            if (!empty($logoPath) && file_exists($logoPath)) {
                unlink($logoPath);
            }
            $logoPath = $newLogoPath;
        } else {
            echo "Error uploading new logo.";
            exit();
        }
    }

    $updateSQL = "UPDATE cocurricular_groups SET group_name=?, group_type=?, group_description=?, logo_path=?, advisor_name=?, advisor_ic=?, president_ic=?, vice_president_ic=?, secretary_ic=?, treasurer_ic=?, total_members=? WHERE group_name=?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param(
        "ssssssssssis",
        $groupName,
        $groupType,
        $groupDescription,
        $logoPath,
        $advisorName,
        $advisorIC,
        $presidentIC,
        $vicePresidentIC,
        $secretaryIC,
        $treasurerIC,
        $totalMembers,
        $originalGroupName
    );

    if ($stmt->execute()) {
        echo "<script>alert('Group updated successfully!'); window.location.href='cocurricular_info.php?group=" . urlencode($groupName) . "';</script>";
        exit();
    } else {
        echo "Error updating group: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Kelab - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/edit_cocuboard.css" />
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
        <div class="form-container">
            <h2>Edit Maklumat Kumpulan</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>Nama Kumpulan</label>
                <input type="text" name="group_name" value="<?= htmlspecialchars($group['group_name']) ?>">

                <label>Jenis Kumpulan</label>
                <div class="group-types">
                    <?php
                    $types = [
                        'uniform_bodies' => 'BADAN BERUNIFORM',
                        'sports' => 'SUKAN',
                        'clubs_associations' => 'KELAB',
                        'others' => 'LAIN_LAIN'
                    ];
                    foreach ($types as $value => $label) {
                        $checked = $group['group_type'] === $value ? 'checked' : '';
                        echo "<label><input type='radio' name='group_type' value='$value' $checked> $label</label>";
                    }
                    ?>
                </div>

                <label>Maklumat Kumpulan</label>
                <textarea name="group_description" rows="4"><?= htmlspecialchars($group['group_description']) ?></textarea>

                <label>ANama Penasihat</label>
                <input type="text" name="advisor_name" value="<?= htmlspecialchars($group['advisor_name']) ?>">

                <label>IC Penasihat</label>
                <input type="text" name="advisor_ic" value="<?= htmlspecialchars($group['advisor_ic']) ?>">

                <label>IC Presiden</label>
                <input type="text" name="president_ic" value="<?= htmlspecialchars($group['president_ic']) ?>">

                <label>IC Naib Presiden</label>
                <input type="text" name="vice_president_ic" value="<?= htmlspecialchars($group['vice_president_ic']) ?>">

                <label>IC Setiausaha</label>
                <input type="text" name="secretary_ic" value="<?= htmlspecialchars($group['secretary_ic']) ?>">

                <label>IC Bendahari</label>
                <input type="text" name="treasurer_ic" value="<?= htmlspecialchars($group['treasurer_ic']) ?>">

                <label>Jumlah Ahli</label>
                <input type="number" name="total_members" value="<?= htmlspecialchars($group['total_members']) ?>">

                <label>Muat Naik Logo (pilihan)</label>
                <input type="file" name="logo">

                <button type="submit" class="submit-button">Update Kumpulan</button>
            </form>
        </div>
    </div>
  
</body>

</html>