<?php
session_start();

// Auto logout after 30 minutes of inactivity
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
  session_unset();
  session_destroy();
  header("Location: ../login.php?expired=1");
  exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time

// Check if user is logged in and is admin
if (!isset($_SESSION['user_ic']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

$admin_username = $_SESSION['user_ic'];

include '../connect.php';
include '../header.php';

// Get current date
$today = date("Y-m-d");

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

// Get events with group names
$query = "
    SELECT e.*, g.group_name 
    FROM events e 
    LEFT JOIN cocurricular_groups g ON e.group_id = g.group_id
    WHERE DATE_ADD(e.event_end_date, INTERVAL 3 DAY) >= ?
    ORDER BY e.event_start_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - SRIAAWP ActivHub</title>
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
        <span class="admin-text"><?php echo strtoupper(htmlspecialchars($admin_username)); ?></span><br>
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
        <p>
        <div class="salam">السلام عليكم</div><?php echo strtoupper(htmlspecialchars($admin_username)); ?></p>
        <button class="btn-yellow" onclick="window.location.href='../audit_history.php'">BORANG SEJARAH</button>
        <button class="btn-yellow" onclick="location.href='admin_list.php'">SENARAI ADMIN</button>
        <button class="btn-yellow" onclick="location.href='admin_classList.php'">SENARAI KELAS</button>
        <button class="btn-yellow" onclick="location.href='../add_events.php'">TAMBAH ACARA KOKURIKULUM</button>
        <button class="btn-yellow" onclick="location.href ='../cocurricular_board.php'">PAPAN KOKURIKULUM</button>
        <form action="../logout.php" method="post">
          <button type="submit" class="btn-red">DAFTAR KELUAR</button>
        </form>
      </div>

      <div class="right-panel">
        <h3>ACARA KOKURIKULUM</h3>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="event-item">
            <strong><?= date("j F Y", strtotime($row['event_start_date'])) ?></strong><br>
            <?= htmlspecialchars($row['event_name']) ?><br>
            Tempat: <?= htmlspecialchars($row['event_venue']) ?><br>
            Pendaftaran: Buka sehingga <?= date("j F", strtotime($row['registration_deadline'])) ?><br>
            Hubungi: <?= htmlspecialchars($row['contact_number']) ?><br>
            Penganjur: <?= $row['group_name'] ? htmlspecialchars($row['group_name']) : 'Acara Luar' ?><br>
            <?php
            $start = strtotime($row['event_start_date']);
            $end = strtotime($row['event_end_date']);
            $now = strtotime($today);
            $status = "";
            $color = "";

            if ($now < $start) {
              $status = "Akan Datang";
              $color = "green";
            } elseif ($now >= $start && $now <= $end) {
              $status = "Sedang Berlangsung";
              $color = "orange";
            } else {
              $status = "Telah Selesai";
              $color = "red";
            }
            ?>
            <p style="color: <?= $color ?>; font-weight: bold;">Status: <?= $status ?></p>

            <button class="btn-status-blue" onclick="location.href='../event_participants.php?event_id=<?= $row['event_id'] ?>'">Senarai Peserta</button>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="manage-users-section">
      <h1>URUS PENGGUNA</h1>
      <div class="manage-users-cards">
        <div class="card">
          <img src="../img/teachers.jpg" alt="Teachers">
          <p style="text-align:center;">Guru-Guru</p>
          <a href="../teacher/teacherList.php">
            <button class="btn-yellow">Select</button>
          </a>
        </div>
        <div class="card">
          <img src="../img/students.jpg" alt="Students">
          <p style="text-align:center;">Murid-Murid</p>
          <a href="admin_studentList.php">
            <button class="btn-yellow">Select</button>
          </a>
        </div>
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
              <?php
              $rank++;
            endwhile;
            while ($rank <= 10):
              ?>
                <tr>
                  <td><?php echo $rank; ?></td>
                  <td>-</td>
                  <td>-</td>
                </tr>
              <?php
              $rank++;
            endwhile;
          else:
            for ($rank = 1; $rank <= 10; $rank++):
              ?>
                <tr>
                  <td><?php echo $rank; ?></td>
                  <td>-</td>
                  <td>-</td>
                </tr>
            <?php endfor;
          endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>