<?php
session_start();
include 'connect.php';
include 'header.php';

if (!isset($_GET['group'])) {
    echo "No group selected.";
    exit();
}

$groupName = urldecode($_GET['group']);

// Delete logic
if ((isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') or (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher')):
    if (isset($_POST['delete'])) {
        // Retrieve logo path before deletion
        $stmt = $conn->prepare("SELECT logo_path FROM cocurricular_groups WHERE group_name = ?");
        $stmt->bind_param("s", $groupName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $groupData = $result->fetch_assoc();
            $logoPath = $groupData['logo_path'];

            // Delete the group
            $delStmt = $conn->prepare("DELETE FROM cocurricular_groups WHERE group_name = ?");
            $delStmt->bind_param("s", $groupName);

            if ($delStmt->execute()) {
                // Remove logo file if exists
                if (!empty($logoPath) && file_exists($logoPath)) {
                    unlink($logoPath);
                }
                echo "<script>alert('Group deleted successfully.'); window.location.href='cocurricular_board.php';</script>";
                exit();
            } else {
                echo "Error deleting group.";
                exit();
            }
        } else {
            echo "Group not found for deletion.";
            exit();
        }
    }
endif;

$sql = "SELECT * FROM cocurricular_groups WHERE group_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $groupName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Group not found.";
    exit();
}

$group = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Info Kokurikulum - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/cocu_board_info.css" />
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
        <h2>PAPAN KOKURIKULUM</h2>
        <div class="info-container">

            <div class="header">
                <div class="spacer"></div>
                <a class="return-button" href="cocurricular_board.php">KEMBALI</a>
            </div>


            <div class="section-title">MAKLUMAT</div>

            <div class="content">
                <img src="<?= htmlspecialchars($group['logo_path']) ?>" alt="Logo" class="group-logo">
                <div>
                    <h3><?= htmlspecialchars($group['group_name']) ?></h3>
                    <p><strong>Penasihat:</strong> <?= htmlspecialchars($group['advisor_name']) ?></p>
                    <p><strong>Jumlah Ahli:</strong> <?= htmlspecialchars($group['total_members']) ?>murid-murid</p>
                </div>
            </div>

            <div class="description">
                <div class="section-title">VISI & MISI</div>
                <p><?= nl2br(htmlspecialchars($group['group_description'])) ?></p>
            </div>


            <div class="section-title">AKTIVITI 2024</div>
            <ul class="activities">
                <li><strong>1. ASAS BANTUAN PERUBATAN</strong><br>17/3/24 - Murid-murid diajar asas-asas bantuan perubatan dalam merawat luka kecil dan besar.</li>
            </ul>

            <div class="section-title">AKTIVITI 2023</div>
            <ul class="activities">
                <li>Akan Datang...</li>
            </ul>

            <?php if ((isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') or (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher')): ?>

                <div class="button-group">
                    <a href="edit_club.php?group=<?= urlencode($group['group_name']) ?>" class="edit-button">Edit Kumpulan</a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this group?');" style="display:inline;">
                        <button type="submit" name="delete" class="delete-button">Buang Kumpulan</button>
                    </form>
                </div>

            <?php endif; ?>


        </div>
    </div>

</body>

</html>