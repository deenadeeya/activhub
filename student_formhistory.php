<?php
session_start();
include 'connect.php';
include 'header.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'student') {
    echo "Akses ditolak. Sila <a href='../login.php'>log masuk</a>.";
    exit;
}

$student_ic = $_SESSION['user_ic'];
$query = "
    SELECT a.*, s.student_fname, s.student_class
    FROM cocu_activities a
    JOIN student s ON a.student_ic = s.student_ic
    WHERE a.student_ic = '$student_ic'
    ORDER BY a.created_at DESC
";

$result = mysqli_query($conn, $query);

// Mark notifications as read for this student
$update_query = "
  UPDATE cocu_activities
  SET notification_read = 1
  WHERE student_ic = '$student_ic' 
    AND approval_status IN ('pending', 'approved', 'rejected') 
    AND notification_read = 0
";
mysqli_query($conn, $update_query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Sejarah Permohonan Ko-kurikulum</title>
    <link rel="stylesheet" href="../css/dash.css" />
    <link rel="stylesheet" href="../css/header&bg.css" />
    <link rel="stylesheet" href="../css/button.css" />
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
    <h1>SEJARAH PERMOHONAN AKTIVITI ANDA</h1>
    <div class="btn-yellow" ><a href="student_dashboard.php">← Kembali ke Papan Pemuka</a></div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="history-table" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                <th>Nama Aktiviti</th>
                <th>Tarikh</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Anugerah</th>
                <th>Sijil</th>
                <th>Status</th>
                <th>Disahkan Oleh</th>
                <th>Tarikh Disahkan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                    <td><?= htmlspecialchars($row['activity_name']) ?></td>
                    <td><?= htmlspecialchars($row['activity_date']) ?></td>
                    <td><?= htmlspecialchars($row['activity_category']) ?></td>
                    <td><?= htmlspecialchars($row['activity_location']) ?></td>
                    <td><?= htmlspecialchars($row['award']) ?></td>
                    <td>
                        <?php if (!empty($row['cert_path'])): ?>
                        <a style="color: white;" href="<?= htmlspecialchars($row['cert_path']) ?>" target="_blank">[Sijil]</a>
                        <?php else: ?>
                        Tiada
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        if ($row['approval_status'] === 'pending') echo 'Dalam Semakan';
                        elseif ($row['approval_status'] === 'approved') echo 'Diluluskan';
                        elseif ($row['approval_status'] === 'rejected') echo 'Ditolak';
                        else echo '-';
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['approved_by'] ?? '-') ?></td>
                    <td><?= $row['approved_at'] ? date('Y-m-d H:i', strtotime($row['approved_at'])) : '-' ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center;">Tiada rekod aktiviti ditemui.</td>
                </tr>
                <?php endif; ?>
            </tbody>
            </table>

    <?php else: ?>
        <p>Tiada permohonan dihantar.</p>
    <?php endif ?>
    </div>
</body>
</html>
