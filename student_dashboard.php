<?php
require_once 'connect.php';
session_start();
include 'header.php';

if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'student') {
  header("Location: login.php?expired=true");
  exit();
}

$user_ic = $_SESSION['user_ic'];
$sql = "SELECT * FROM student WHERE student_ic='$user_ic'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) === 1) {
  $row = mysqli_fetch_assoc($result);
} else {
  header("Location: login.php?expired=true");
  exit();
}

// Pending Notification
$query = "
  SELECT COUNT(*) AS pending_count 
  FROM cocu_activities 
  WHERE student_ic = '$user_ic' 
    AND approval_status IN ('pending', 'approved', 'rejected')
    AND notification_read = 0
";

$result = mysqli_query($conn, $query);
$pending_count = 0;

if ($result && $row_pending = mysqli_fetch_assoc($result)) {
  $pending_count = $row_pending['pending_count'];
}

// Get leaderboard (top 10 students with most activities)
$leaderboard_query = "
    SELECT s.student_fname, s.student_class, COUNT(a.id) AS total_activities
    FROM student s
    LEFT JOIN cocu_activities a ON s.student_ic = a.student_ic AND a.approval_status = 'approved'
    GROUP BY s.student_ic
    ORDER BY total_activities DESC
    LIMIT 10
";
$leaderboard_result = mysqli_query($conn, $leaderboard_query);



// Registration handling
$registration_success = false;
if (isset($_GET['register']) && isset($_GET['event_id'])) {
  $event_id = intval($_GET['event_id']);
  $check_query = "SELECT * FROM event_registrations WHERE student_id = '$user_ic' AND event_id = $event_id";
  $check_result = mysqli_query($conn, $check_query);

  if (mysqli_num_rows($check_result) === 0) {
    $register_query = "INSERT INTO event_registrations (student_id, event_id) VALUES ('$user_ic', $event_id)";
    mysqli_query($conn, $register_query);
    $registration_success = true;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Murid Dashboard - SRIAAWP ActivHub</title>
  <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../css/dash.css" />
  <link rel="stylesheet" href="../css/header&bg.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
  <script>
    function confirmRegistration(eventId) {
      if (confirm("Adakah anda pasti mahu mendaftar untuk acara ini?")) {
        window.location.href = `student_dashboard.php?register=true&event_id=${eventId}`;
      }
    }
  </script>
</head>

<body>
  <?php if ($registration_success): ?>
    <script>
      alert("Pendaftaran berjaya!");
    </script>
  <?php endif; ?>

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
        <p><div class="salam">السلام عليكم</div><?php echo strtoupper($row['student_fname']); ?></p>
        <button class="btn-yellow" onclick="location.href='student_formhistory.php'" style="position: relative;">
          SEJARAH BORANG
          <?php if ($pending_count > 0): ?>
            <span style="
              position: absolute;
              top: -5px;
              right: -5px;
              background: red;
              color: white;
              border-radius: 50%;
              padding: 4px 7px;
              font-size: 12px;
            ">
              <?php echo $pending_count; ?>
            </span>
          <?php endif; ?>
        </button>

        <button class="btn-yellow" onClick="location.href='student_profile.php';">PROFIL MURID</button>
        <button class="btn-yellow" onClick="location.href='student_cocurricular.php';">PROFIL & AKTIVITI KOKURIKULUM</button>
        <button class="btn-yellow" onClick="location.href='cocurricular_board.php';">PAPAN KOKURIKULUM</button>
        <form action="logout.php" method="post">
          <button type="submit" class="btn-red">DAFTAR KELUAR</button>
        </form>
      </div>

      <div class="right-panel">
        <h3>ACARA KOKURIKULUM</h3>

        <?php
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $today = date('Y-m-d');
        $now = strtotime($today);

        $events_query = "
          SELECT e.*, g.group_name
          FROM events e
          LEFT JOIN cocurricular_groups g ON e.group_id = g.group_id
          ORDER BY e.event_start_date DESC
        ";
        $events_result = mysqli_query($conn, $events_query);

        if ($events_result && mysqli_num_rows($events_result) > 0):
          while ($event = mysqli_fetch_assoc($events_result)):
            $start = strtotime($event['event_start_date']);
            $end = strtotime($event['event_end_date']);
            $deadline = $event['registration_deadline'];
            $status = '';
            $color = '';
            $show = true;

            $days_after_end = ($now - $end) / (60 * 60 * 24);
            if ($days_after_end > 3) {
              $show = false;
            } elseif ($now < $start) {
              $status = 'Akan Datang';
              $color = 'green';
            } elseif ($now >= $start && $now <= $end) {
              $status = 'Sedang Berlangsung';
              $color = 'orange';
            } else {
              $status = 'Telah Selesai';
              $color = 'red';
            }

            if (!$show) continue;

            // Check if user is already registered
            $check_reg = "SELECT * FROM event_registrations WHERE student_id = '$user_ic' AND event_id = {$event['event_id']}";
            $reg_result = mysqli_query($conn, $check_reg);
            $is_registered = mysqli_num_rows($reg_result) > 0;
        ?>

          <div class="event-item">
            <strong><?php echo date('d M Y', strtotime($event['event_start_date'])); ?></strong><br>
            <?php echo strtoupper(htmlspecialchars($event['event_name'])); ?><br>
            Tempat: <?php echo htmlspecialchars($event['event_venue']); ?><br>
            Penganjur: <?= $event['group_name'] ? htmlspecialchars($event['group_name']) : 'Acara Luar' ?><br>

            <?php if ($deadline): ?>
              Pendaftaran: Buka sehingga <?php echo date('d M Y', strtotime($deadline)); ?><br>
            <?php endif; ?>

            <?php if ($event['contact_number']): ?>
              Hubungi: <?php echo htmlspecialchars($event['contact_number']); ?><br>
            <?php endif; ?>

            <p style="color: <?= $color ?>; font-weight: bold;">Status: <?= $status ?></p>

            <?php if ($now < $start && !$is_registered): ?>
              <button class="btn-status-blue" onclick="confirmRegistration(<?= $event['event_id'] ?>)">Register Here</button>
            <?php elseif ($is_registered): ?>
              <span class="btn-status-blue-registered">✅ Telah Berdaftar</span>
            <?php endif; ?>
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
              <?php $rank++;
            endwhile;
            while ($rank <= 10): ?>
                <tr>
                  <td><?php echo $rank; ?></td>
                  <td>-</td>
                  <td>-</td>
                </tr>
              <?php $rank++;
            endwhile;
          else:
            for ($rank = 1; $rank <= 10; $rank++): ?>
                <tr>
                  <td><?php echo $rank; ?></td>
                  <td>-</td>
                  <td>-</td>
                </tr>
              <?php endfor; ?>
            <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>