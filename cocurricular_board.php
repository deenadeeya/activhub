<?php
session_start();
include 'connect.php';
include 'header.php';

$pending_count = 0;

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student') {
    // Set student_ic from session
    $student_ic = $_SESSION['user_ic'] ?? null;

    if ($student_ic) {
        // Only for students: count pending notifications
        $query = "
          SELECT COUNT(*) AS pending_count 
          FROM cocu_activities 
          WHERE student_ic = ? 
            AND approval_status IN ('pending', 'approved', 'rejected')
            AND notification_read = 0
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $student_ic);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row_pending = $result->fetch_assoc()) {
            $pending_count = $row_pending['pending_count'];
        }

        $stmt->close();
    }
}

// Teacher notification count logic
$teacher_class_id = null;
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher') {
    $teacher_ic = $_SESSION['user_ic'];
    $sql_class_id = "SELECT class_id FROM class WHERE head_teacher = '$teacher_ic'";
    $result_class_id = mysqli_query($conn, $sql_class_id);
    if ($result_class_id && mysqli_num_rows($result_class_id) > 0) {
        $row_class_id = mysqli_fetch_assoc($result_class_id);
        $teacher_class_id = $row_class_id['class_id'];
    }
}
// Pending approval count for teacher
if ($teacher_class_id) {
    $pending_query = "
            SELECT COUNT(*) AS total_pending
            FROM cocu_activities ca
            JOIN student s ON ca.student_ic = s.student_ic
            WHERE ca.approval_status = 'pending' AND s.student_class = ?
        ";
    $stmt = $conn->prepare($pending_query);
    $stmt->bind_param("s", $teacher_class_id);
    $stmt->execute();
    $pending_result = $stmt->get_result();
    $pending_data = $pending_result->fetch_assoc();
    $pending_count = $pending_data['total_pending'];
}
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
      <?php
        // Replace with your actual notification count variable
        $notif_count = $pending_count;
        $notif_link = "student_formhistory.php";
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher') {
            $notif_link = "approve_form.php";
        }
        ?>

        <button onclick="location.href='<?php echo $notif_link; ?>'" style="position: relative; background: none; border: none; cursor: pointer;">
          <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
            notifications
          </span>

          <?php if ($notif_count > 0): ?>
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
              <?php echo $notif_count; ?>
            </span>
          <?php endif; ?>
        </button>
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
        'others' => 'LAIN-LAIN'
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
