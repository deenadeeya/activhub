<?php
session_start();
include 'connect.php';
include 'header.php';

if (!isset($_GET['group'])) {
    echo "No group selected.";
    exit();
}

$groupName = urldecode($_GET['group']);

// Fetch group info first to get the group ID
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
$groupId = $group['group_id'];

// === HANDLE MEMBER REMOVAL POST ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Remove member logic
    if (isset($_POST['remove_member_ic'], $_POST['remove_member_group'])) {
        // Only allow if admin or teacher
        if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')) {
            $removeIc = $_POST['remove_member_ic'];
            $removeGroupId = (int)$_POST['remove_member_group'];

            if ($removeGroupId === $groupId) {
                $deleteStmt = $conn->prepare("DELETE FROM student_club_membership WHERE student_ic = ? AND group_id = ?");
                $deleteStmt->bind_param("si", $removeIc, $removeGroupId);
                if ($deleteStmt->execute()) {
                    // Redirect to refresh the page after deletion
                    header("Location: cocurricular_info.php?group=" . urlencode($groupName));
                    exit();
                } else {
                    echo "<script>alert('Failed to remove member.');</script>";
                }
            }
        }
    }

    // Add member logic
    if (isset($_POST['add_member'])) {
        $student_ic = $_POST['student_ic'];
        $role = 'Ahli';

        $checkStmt = $conn->prepare("SELECT * FROM student_club_membership WHERE student_ic = ? AND group_id = ?");
        $checkStmt->bind_param("si", $student_ic, $groupId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            $insertStmt = $conn->prepare("INSERT INTO student_club_membership (student_ic, group_id, membership_role) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sis", $student_ic, $groupId, $role);
            $insertStmt->execute();
        }
        header("Location: cocurricular_info.php?group=" . urlencode($groupName));
        exit();
    }

    // Delete group logic
    if (isset($_POST['delete'])) {
        if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')) {
            $logoPath = $group['logo_path'];
            $delStmt = $conn->prepare("DELETE FROM cocurricular_groups WHERE group_id = ?");
            $delStmt->bind_param("i", $groupId);

            if ($delStmt->execute()) {
                if (!empty($logoPath) && file_exists($logoPath)) {
                    unlink($logoPath);
                }
                echo "<script>alert('Group deleted successfully.'); window.location.href='cocurricular_board.php';</script>";
                exit();
            } else {
                echo "Error deleting group.";
                exit();
            }
        }
    }
}

// Count members
$countStmt = $conn->prepare("SELECT COUNT(*) AS member_count FROM student_club_membership WHERE group_id = ?");
$countStmt->bind_param("i", $groupId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalMembers = $countResult->fetch_assoc()['member_count'];

// Fetch group events
$activityQuery = $conn->prepare("SELECT event_name, event_start_date, event_end_date, event_venue FROM events WHERE group_id = ?");
$activityQuery->bind_param("i", $groupId);
$activityQuery->execute();
$activityResult = $activityQuery->get_result();

// Fetch group members with student_ic (needed for removal form)
$memberQuery = $conn->prepare("SELECT s.student_ic, s.student_fname, scm.membership_role FROM student_club_membership scm JOIN student s ON scm.student_ic = s.student_ic WHERE scm.group_id = ?");
$memberQuery->bind_param("i", $groupId);
$memberQuery->execute();
$memberResult = $memberQuery->get_result();

// Fetch students not yet members
$nonMemberQuery = $conn->prepare("
    SELECT s.student_ic, s.student_fname
    FROM student s
    WHERE s.student_ic NOT IN (
        SELECT student_ic FROM student_club_membership WHERE group_id = ?
    )
");
$nonMemberQuery->bind_param("i", $groupId);
$nonMemberQuery->execute();
$nonMemberResult = $nonMemberQuery->get_result();
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Kokurikulum - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="css/header&bg.css">
    <link rel="stylesheet" href="css/cocu_board_info.css">
    <link rel="stylesheet" href="css/button.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
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
                    <p><strong>Jumlah Ahli:</strong> <?= $totalMembers ?> murid-murid</p>
                </div>
            </div>

            <div class="description">
                <div class="section-title">VISI & MISI</div>
                <p><?= nl2br(htmlspecialchars($group['group_description'])) ?></p>
            </div>

            <div class="section-title">AKTIVITI</div>
            <ul class="activities">
                <?php
                $activityCount = 0;
                while ($activity = $activityResult->fetch_assoc()):
                    $activityCount++;
                ?>
                    <li>
                        <strong><?= $activityCount ?>. <?= htmlspecialchars($activity['event_name']) ?></strong><br>
                        <?= date('d/m/Y', strtotime($activity['event_start_date'])) ?> to <?= date('d/m/Y', strtotime($activity['event_end_date'])) ?><br>
                        Venue: <?= htmlspecialchars($activity['event_venue']) ?>
                    </li>
                <?php endwhile; ?>
                <?php if ($activityCount === 0): ?>
                    <li>Tiada aktiviti diluluskan setakat ini.</li>
                <?php endif; ?>
            </ul>

            <div class="section-title">AHLI KUMPULAN</div>
            <table class="member-table">
                <thead>
                    <tr>
                        <th>Nama Pelajar</th>
                        <th>Jawatan</th>
                        <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')): ?>
                            <th>Tindakan</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($member = $memberResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($member['student_fname']) ?></td>
                            <td><?= htmlspecialchars($member['membership_role'] ?: 'Ahli') ?></td>
                            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')): ?>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Remove this member?');" style="margin:0;">
                                        <input type="hidden" name="remove_member_ic" value="<?= htmlspecialchars($member['student_ic']) ?>">
                                        <input type="hidden" name="remove_member_group" value="<?= $groupId ?>">
                                        <button type="submit" class="delete-button">Remove</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>

            <?php if ((isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') || $_SESSION['user_role'] === 'teacher'): ?>
                <div class="section-title">TAMBAH AHLI BIASA</div>
                <div style="text-align:center;">
                    <form method="POST" style="display:inline-block">
                        <select name="student_ic" required>
                            <option value="">-- Pilih Pelajar --</option>
                            <?php while ($row = $nonMemberResult->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['student_ic']) ?>"><?= htmlspecialchars($row['student_fname']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" name="add_member" class="edit-button">Tambah</button>
                    </form>
                </div>
                <div class="button-group">
                    <a href="edit_club.php?group_id=<?= $group['group_id'] ?>" class="edit-button">Edit Kumpulan</a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this group?');" style="display:inline;">
                        <button type="submit" name="delete" class="delete-button">Buang Kumpulan</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>