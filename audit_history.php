<?php
session_start();
include 'connect.php';
include 'header.php';

if (!isset($_SESSION['user_ic']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    echo "Unauthorized access. Please <a href='../login.php'>login again</a>.";
    exit;
}

$user_ic = $_SESSION['user_ic'];
$user_role = $_SESSION['user_role'];

// Reapprove logic for rejected forms
if (isset($_POST['reapprove']) && isset($_POST['activity_id'])) {
    $activity_id = $_POST['activity_id'];
    $now = date('Y-m-d H:i:s');
    $reapproveQuery = "
        UPDATE cocu_activities
        SET approval_status = 'approved', approved_by = '$user_ic', approved_at = '$now'
        WHERE id = '$activity_id'
    ";
    mysqli_query($conn, $reapproveQuery);
}

// Get relevant applications
if ($user_role === 'teacher') {
    // Get teacher's class
    $classQuery = "SELECT class FROM teacher WHERE teacher_ic = '$user_ic'";
    $classResult = mysqli_query($conn, $classQuery);
    $teacherClass = mysqli_fetch_assoc($classResult)['class'];

    $query = "
        SELECT a.*, s.student_fname, s.student_class
        FROM cocu_activities a
        JOIN student s ON a.student_ic = s.student_ic
        WHERE s.student_class = '$teacherClass'
        ORDER BY a.created_at DESC
    ";
} else {
    // Admin sees all
    $query = "
        SELECT a.*, s.student_fname, s.student_class
        FROM cocu_activities a
        JOIN student s ON a.student_ic = s.student_ic
        ORDER BY a.created_at DESC
    ";

    }

$teacher_ic = $_SESSION['user_ic'];

$sql = "SELECT * FROM teacher WHERE teacher_ic = '$teacher_ic'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  $teacher = mysqli_fetch_assoc($result);
  $teacher_class = $teacher['class'];
}

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sejarah Borang Kokurikulum - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/cocurricular.css" />
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
    <h2>Sejarah Permohonan Aktiviti Pelajar</h2>
    <div class="btn-yellow"><a href="<?= $user_role === 'teacher' ? '../teacher/teacher_dashboard.php' : '../admin/admin_dashboard.php' ?>">← Kembali ke Papan Pemuka</a></div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Nama Pelajar</th>
                    <th>Kelas</th>
                    <th>IC</th>
                    <th>Nama Aktiviti</th>
                    <th>Tarikh Aktiviti</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Anugerah</th>
                    <th>Sijil</th>
                    <th>Status</th>
                    <th>Disahkan Oleh</th>
                    <th>Tarikh Disahkan</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_fname']) ?></td>
                        <td><?= htmlspecialchars($row['student_class']) ?></td>
                        <td><?= htmlspecialchars($row['student_ic']) ?></td>
                        <td><?= htmlspecialchars($row['activity_name']) ?></td>
                        <td><?= htmlspecialchars($row['activity_date']) ?></td>
                        <td><?= htmlspecialchars($row['activity_category']) ?></td>
                        <td><?= htmlspecialchars($row['activity_location']) ?></td>
                        <td><?= htmlspecialchars($row['award']) ?></td>
                        <td>
                            <?php if (!empty($row['cert_path'])): ?>
                                <a href="<?= htmlspecialchars($row['cert_path']) ?>" target="_blank">[Sijil]</a>
                            <?php else: ?>
                                Tiada
                            <?php endif; ?>
                        </td>
                        <td><?= ucfirst($row['approval_status']) ?></td>
                        <td><?= htmlspecialchars($row['approved_by'] ?? '-') ?></td>
                        <td><?= $row['approved_at'] ? date('Y-m-d H:i', strtotime($row['approved_at'])) : '-' ?></td>
                        <td>
                            <?php if (in_array($user_role, ['teacher', 'admin']) && $row['approval_status'] === 'rejected'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="activity_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="reapprove">Reapprove</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tiada sejarah borang ditemui.</p>
    <?php endif ?>
    </div>
</body>
</html>
