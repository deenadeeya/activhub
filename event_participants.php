<?php
require_once 'connect.php';
session_start();
include 'header.php';

// Validate and fetch the event_id
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo "Invalid event ID.";
    exit();
}

$event_id = intval($_GET['event_id']);

// Fetch event name
$event_sql = "SELECT event_name FROM events WHERE event_id = $event_id";
$event_result = mysqli_query($conn, $event_sql);

if (!$event_result || mysqli_num_rows($event_result) === 0) {
    echo "Event not found.";
    exit();
}

$event = mysqli_fetch_assoc($event_result);
$event_name = $event['event_name'];

// Fetch registered students
$students_sql = "
    SELECT s.student_fname, s.student_class, s.student_ic
    FROM event_registrations er
    JOIN student s ON er.student_id = s.student_ic
    WHERE er.event_id = $event_id
    ORDER BY s.student_fname
";

$students_result = mysqli_query($conn, $students_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Peserta Acara - <?php echo htmlspecialchars($event_name); ?></title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/cocurricular.css" />
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
        <h1>Senarai Peserta: <?php echo strtoupper(htmlspecialchars($event_name)); ?></h1>

        <?php if ($students_result && mysqli_num_rows($students_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th class="rank">No.</th>
                        <th class="student">Nama Pelajar</th>
                        <th class="total">Kelas</th>
                        <th class="total">No. Kad Pengenalan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    while ($student = mysqli_fetch_assoc($students_result)):
                    ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($student['student_fname']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_class']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_ic']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p>Tiada pelajar yang telah mendaftar untuk acara ini.</p>
        <?php endif; ?>
    </div>
</body>

</html>