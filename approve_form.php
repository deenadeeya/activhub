<?php
session_start();
include 'connect.php';
include 'header.php';

if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    echo "Unauthorized access. Please <a href='../login.php'>login again</a>.";
    exit;
}

$teacher_ic = $_SESSION['user_ic'];

// Get teacher's class
$classQuery = "SELECT class FROM teacher WHERE teacher_ic = '$teacher_ic'";
$classResult = mysqli_query($conn, $classQuery);
$teacherClass = mysqli_fetch_assoc($classResult)['class'];

// Handle approval action
if (isset($_POST['approve']) && isset($_POST['activity_id'])) {
    $activity_id = $_POST['activity_id'];
    $currentDateTime = date('Y-m-d H:i:s');

    // Update approval_status, approved_by, approved_at, reset notification_read
    $updateQuery = "
        UPDATE cocu_activities 
        SET 
            approval_status = 'approved',
            approved_by = '$teacher_ic',
            approved_at = '$currentDateTime',
            notification_read = 0
        WHERE id = '$activity_id'
    ";
    mysqli_query($conn, $updateQuery);
}

// Handle cancellation action
if (isset($_POST['cancel']) && isset($_POST['activity_id'])) {
    $activity_id = $_POST['activity_id'];

    $updateQuery = "
        UPDATE cocu_activities 
        SET 
            approval_status = 'rejected', 
            approved_by = NULL, 
            approved_at = NULL,
            notification_read = 0
        WHERE id = '$activity_id'
    ";
    mysqli_query($conn, $updateQuery);
}


// Get unapproved applications for teacher's class
$query = "
    SELECT a.id, s.student_fname, s.student_ic, a.activity_name, a.activity_date, a.activity_category, a.award, a.cert_path
    FROM cocu_activities a
    JOIN student s ON a.student_ic = s.student_ic
    WHERE s.student_class = '$teacherClass' AND a.approval_status = 'pending'
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Borang - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="css/dash.css"/>
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/button.css"/>
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

    <h1>Borang Pelajar Untuk Diluluskan</h1>
    <button class="btn-yellow"><a href="../teacher/teacher_dashboard.php">← Kembali ke Papan Pemuka</a></button>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Nama Pelajar</th>
                    <th>IC</th>
                    <th>Nama Aktiviti</th>
                    <th>Tarikh Aktiviti</th>
                    <th>Kategori Aktiviti</th>
                    <th>Sijil</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_fname']) ?></td>
                        <td><?= htmlspecialchars($row['student_ic']) ?></td>
                        <td><?= htmlspecialchars($row['activity_name']) ?></td>
                        <td><?= htmlspecialchars($row['activity_date']) ?></td>
                        <td><?= htmlspecialchars($row['activity_category']) ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['award']); ?>
                                <?php if (!empty($row['cert_path'])): ?>
                                    <br><a href="<?php echo $row['cert_path']; ?>" target="_blank">[Sijil]</a>
                                <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="activity_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="approve">Luluskan</button>
                                <button type="submit" name="cancel" style="margin-left: 10px;">Batalkan</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tiada borang yang belum diluluskan.</p>
    <?php endif ?>
    </div>
</body>
</html>
