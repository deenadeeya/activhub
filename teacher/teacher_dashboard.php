<?php
require_once '../connect.php';
session_start();
include '../header.php';

if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
  header("Location: ../login.php?expired=true");
  exit();
}

$teacher_ic = $_SESSION['user_ic'];

$sql = "SELECT * FROM teacher WHERE teacher_ic = '$teacher_ic'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  $teacher = mysqli_fetch_assoc($result);
  $teacher_class = $teacher['class'];

  $pending_count_query = "
    SELECT COUNT(*) as total_pending
    FROM cocu_activities ca
    JOIN student s ON ca.student_ic = s.student_ic
    WHERE ca.approval_status = 'pending' AND s.student_class = $teacher_class
  ";
  $pending_result = mysqli_query($conn, $pending_count_query);
  $pending_count = 0;
  if ($pending_result) {
    $pending_data = mysqli_fetch_assoc($pending_result);
    $pending_count = $pending_data['total_pending'];
  }

  $leaderboard_query = "
    SELECT s.student_fname, s.student_class, COUNT(a.id) AS total_activities
    FROM student s
    LEFT JOIN cocu_activities a ON s.student_ic = a.student_ic AND a.approval_status = 'approved'
    GROUP BY s.student_ic
    ORDER BY total_activities DESC
    LIMIT 10
  ";
  $leaderboard_result = mysqli_query($conn, $leaderboard_query);
} else {
  header("Location: ../login.php?expired=true");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Guru Dashboard - SRIAAWP ActivHub</title>
  <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../css/dash.css" />
  <link rel="stylesheet" href="../css/header&bg.css" />
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
      <div class="user-section">
        <span class="admin-text"><?php echo strtoupper($teacher['teacher_fname']); ?></span><br>
        <span class="welcome-text">Selamat Kembali!</span>
      </div>
      <span class="material-symbols-outlined icon">notifications</span>
    </div>
  </header>

  <div class="container">
    <div class="welcome-section">
      <img src="../img/logo.png" alt="Logo">
      <div class="welcome-texts">
        <h1>Selamat Datang ke SRIAAWP ActivHub</h1>
        <h2>"Pusat Rekod Kokurikulum Pelajar SRI AL-AMIN WILAYAH PERSEKUTUAN"</h2>
      </div>
    </div>

    <br>
    <div class="dashboard-content">
      <div class="left-panel card">
        <p><div class="salam">السلام عليكم</div><?php echo strtoupper($teacher['teacher_fname']); ?></p>
        <button class="btn-yellow" onclick="window.location.href='../audit_history.php'">BORANG SEJARAH</button>
        <button class="btn-yellow" onclick="window.location.href='../teacher/teacher_profile.php'">PROFIL GURU</button>
        <button class="btn-yellow" onclick="window.location.href='../studentList.php'">SENARAI PELAJAR</button>
        <button class="btn-yellow" onclick="location.href='../approve_form.php'" style="position: relative;">
          SENARAI BORANG
          <?php if ($pending_count > 0): ?>
            <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 4px 7px; font-size: 12px;">
              <?php echo $pending_count; ?>
            </span>
          <?php endif; ?>
        </button>

        <button class="btn-yellow" onclick="location.href='../add_events.php'">TAMBAH ACARA KOKURIKULUM</button>
        <button class="btn-yellow" onclick="location.href ='../cocurricular_board.php'">PAPAN KOKURIKULUM</button>
        <form action="../logout.php" method="post">
          <button type="submit" class="btn-red">DAFTAR KELUAR</button>
        </form>
      </div>

      <div class="right-panel">
        <h3>ACARA KOKURIKULUM</h3>

        <?php
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $today = date('Y-m-d');
        $now = strtotime($today);

        $event_query = "
          SELECT e.*, g.group_name
          FROM events e
          LEFT JOIN cocurricular_groups g ON e.group_id = g.group_id
          ORDER BY e.event_start_date DESC
        ";
        $event_result = mysqli_query($conn, $event_query);

        if ($event_result && mysqli_num_rows($event_result) > 0):
          while ($event = mysqli_fetch_assoc($event_result)):

            $start = strtotime($event['event_start_date']);
            $end = strtotime($event['event_end_date']);
            $deadline = $event['registration_deadline'];
            $status = '';
            $color = '';
            $show = true;

            if ($now < $start) {
              $status = 'Akan Datang';
              $color = 'green';
            } elseif ($now >= $start && $now <= $end) {
              $status = 'Sedang Berlangsung';
              $color = 'orange';
            } else {
              $days_since_end = ($now - $end) / (60 * 60 * 24);
              if ($days_since_end > 3) {
                $show = false;
              } else {
                $status = 'Telah Selesai';
                $color = 'red';
              }
            }

            if (!$show) continue;

            // Check if students from teacher's class registered
            $check_participants = "
              SELECT COUNT(*) AS total
              FROM event_registrations er
              JOIN student s ON er.student_id = s.student_ic
              WHERE er.event_id = {$event['event_id']} AND s.student_class = $teacher_class
            ";
            $reg_result = mysqli_query($conn, $check_participants);
            $reg_data = mysqli_fetch_assoc($reg_result);
            $is_registered = $reg_data['total'] > 0;
        ?>

          <div class="event-item">
            <strong><?php echo date('d M Y', strtotime($event['event_start_date'])); ?></strong><br>
            <?php echo strtoupper($event['event_name']); ?><br>
            Tempat: <?php echo htmlspecialchars($event['event_venue']); ?><br>
            Penganjur: <?= $event['group_name'] ? htmlspecialchars($event['group_name']) : 'Acara Luar' ?><br>
            <?php if ($deadline): ?>
              Pendaftaran: Buka sehingga <?php echo date('d M Y', strtotime($deadline)); ?><br>
            <?php endif; ?>
            <?php if ($event['contact_number']): ?>
              Hubungi: <?php echo htmlspecialchars($event['contact_number']); ?><br>
            <?php endif; ?>

            <p style="color: <?= $color ?>; font-weight: bold;">Status: <?= $status ?></p>

            <button class="btn-status-blue" onclick="window.location.href='../event_participants.php?event_id=<?php echo $event['event_id']; ?>'">Senarai Peserta</button>

            
          </div>

        <?php
          endwhile;
        else:
        ?>
          <p>Tiada acara tersedia buat masa ini.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="leaderboard">
      <h1>PAPAN PENDAHULU</h1>
      <h3>“10 Pelajar Terbaik Dengan Jumlah Aktiviti Kokurikulum Terbanyak Bulan Ini”</h3>
      <table>
        <thead>
          <tr>
            <th class="rank">TEMPAT</th>
            <th class="student">NAMA MURID</th>
            <th class="total">JUMLAH AKTIVITI</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rank = 1;
          if ($leaderboard_result && mysqli_num_rows($leaderboard_result) > 0):
            while ($leader = mysqli_fetch_assoc($leaderboard_result)):
          ?>
              <tr<?php if ($rank === 1) echo ' class="top"'; ?>>
                <td><?php echo $rank; ?></td>
                <td><?php echo htmlspecialchars($leader['student_fname']); ?></td>
                <td><?php echo $leader['total_activities']; ?></td>
              </tr>
              <?php $rank++; endwhile;
              while ($rank <= 10): ?>
                <tr><td><?php echo $rank; ?></td><td>-</td><td>-</td></tr>
              <?php $rank++; endwhile;
          else:
            for ($rank = 1; $rank <= 10; $rank++): ?>
              <tr><td><?php echo $rank; ?></td><td>-</td><td>-</td></tr>
          <?php endfor; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function disableBack() {
      window.history.forward();
    }
    window.onload = disableBack;
    window.onpageshow = function(evt) {
      if (evt.persisted) disableBack();
    };
  </script>

</body>
</html>
