<?php
session_start();
include 'connect.php';
include 'header.php';
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Papan Kokurikulum - SRIAAWP ActivHub</title>
  <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="css/header&bg.css" />
  <link rel="stylesheet" href="css/cocu_board.css" />
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
    <h1 class="profile-title">PAPAN KOKURIKULUM</h1>
    <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'teacher'])): ?>
      <button class="btn-green" onClick="location.href='add_club.php';">TAMBAH PAPAN KOKURIKULUM</button>
    <?php endif; ?>

    <div class="board-container">
      <?php
      $types = [
        'uniform_bodies' => 'BADAN BERUNIFORM',
        'sports' => 'SUKAN',
        'clubs_associations' => 'KELAB',
        'others' => 'LAIN_LAIN'
      ];

      foreach ($types as $key => $title) {
        echo '<div class="category">';
        echo "<h3>$title</h3>";

        $sql = "SELECT group_name, logo_path FROM cocurricular_groups WHERE group_type = '$key'";
        $result = mysqli_query($conn, $sql);

        while ($group_row = mysqli_fetch_assoc($result)) {
          $groupName = $group_row['group_name'];
          $logoPath = $group_row['logo_path'];
          echo '<div class="group">';
          echo "<img src='$logoPath' alt='Logo'>";
          echo "<a href='cocurricular_info.php?group=" . urlencode($groupName) . "'>" . htmlspecialchars($groupName) . "</a>";
          echo '</div>';
        }

        echo '</div>';
      }
      ?>
    </div>
  </div>

</body>
</html>
