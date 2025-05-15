<?php
require_once 'connect.php';
session_start();

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

$leaderboard_query = "
  SELECT s.student_fname, s.student_class, COUNT(a.id) AS total_activities
  FROM student s
  LEFT JOIN cocu_activities a ON s.student_ic = a.student_ic
  GROUP BY s.student_ic
  ORDER BY total_activities DESC
  LIMIT 10
";
$leaderboard_result = mysqli_query($conn, $leaderboard_query);


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SRIAAWP ActivHub</title>
  <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../css/admin_dash.css" />
  <link rel="stylesheet" href="../css/styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
</head>

<body>

<header>
    <div class="logo-section">
      <img src="../img/logo.png" alt="Logo" />
      <div class="logo-text">
        <span>SRIAAWP ActivHub</span>
        <div class="nav-links">
          <a href="student_dashboard.php">Papan Pemuka</a>
          <a href="student_profile.php">Profil</a>
          <a href="#">Papan Kokurikulum</a>
        </div>
      </div>
    </div>
    <div class="icon-section">
      <div class="admin-section">
        <span class="admin-text"><?php echo strtoupper($row['student_fname']); ?></span><br>
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
        <p>HELLO,<br> <?php echo strtoupper($row['student_fname']); ?></p>
        <button class="btn-yellow">PETI MASUK</button>
        <button class="btn-yellow" onClick="location.href='student_profile.php';">PROFIL</button>
        <button class="btn-yellow" onClick="location.href='student_cocurricular.php';">PROFIL & AKTIVITI KOKURIKULUM</button>
        <button class="btn-yellow">PAPAN KOKURIKULUM</button>
        <form action="logout.php" method="post">
          <button type="submit" class="btn-red">DAFTAR KELUAR</button>
        </form>

      </div>

      <div class="right-panel">
        <h3>ACARA KOKURIKULUM</h3>

        <div class="event-item">
          <strong>12th Januari 2025</strong><br>
          PERTANDINGAN TAEKWONDO <br>
          Tempat: SRI AL-AMIN WP<br>
          Pendaftaran: Buka sehingga 16 Disember<br>
          Hubungi: 019-xxxxxxxx<br>
          <button class="btn-status-green">Akan Datang</button>
          <button class="btn-status-blue">Register Here</button>
        </div>

        <div class="event-item">
          <strong>12 Januari 2025</strong><br>
          GOTONG ROYONG MEMBERSIHKAN PANTAI <br>
          Tempat: Pantai Morib<br>
          Pendaftaran: Buka sehingga 10 November<br>
          Hubungi: 019-xxxxxxxx<br>
          <button class="btn-status-red">Telah Berlangsung</button>
        </div>

        <div class="event-item">
          <strong>12th January 2025</strong><br>
          Pertandingan Bola Sepak <br>
          Lokasi: Stadium Bukit Jalil<br>
          Pendaftaran: Buka Sehingga 10 November<br>
          Hubungi: 019-xxxxxxxx<br>
          <button class="btn-status-red">Telah Berlangsung</button>
        </div>
      </div>
    </div>
    
  <div class="leaderboard">
    <h1>LEADERBOARD</h1>
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
            <td>-</td>
        </tr>
    <?php endfor; ?>
    <?php endif; ?>
  </tbody>
</table>

  </div>
</body>
</html>



  </div>

</body>

</html>