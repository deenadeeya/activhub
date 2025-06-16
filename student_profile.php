<?php
session_start();
include 'connect.php';
include 'header.php';

if (!isset($_SESSION['user_ic'])) {
  header("Location: login.php?expired=true");
  exit;
}

$student_ic = $_SESSION['user_ic'];

if ($student_ic) {
  $query = "
    SELECT s.*, c.class_name, t.teacher_fname, t.teacher_email 
    FROM student s 
    JOIN class c ON s.student_class = c.class_id 
    JOIN teacher t ON c.head_teacher = t.teacher_ic 
    WHERE s.student_ic = ?
  ";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $student_ic);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
  } else {
    echo "No student data found.";
    exit;
  }
}

// Pending Notification
$query = "
  SELECT COUNT(*) AS pending_count 
  FROM cocu_activities 
  WHERE student_ic = '$student_ic' 
    AND approval_status IN ('pending', 'approved', 'rejected')
    AND notification_read = 0
";

$result = mysqli_query($conn, $query);
$pending_count = 0;

if ($result && $row_pending = mysqli_fetch_assoc($result)) {
  $pending_count = $row_pending['pending_count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil Murid - SRIAAWP ActivHub</title>
  <link rel="stylesheet" href="css/profile.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
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
        <span class="admin-text"><?php echo strtoupper($row['student_fname']); ?></span><br>
        <span class="welcome-text">Selamat Kembali!</span>
      </div>
      <?php
        // Replace with your actual notification count variable
        $notif_count = $pending_count;
        ?>

        <button onclick="location.href='student_formhistory.php'" style="position: relative; background: none; border: none; cursor: pointer;">
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
    <h1 class="profile-title">PROFIL</h1>

    <main class="profile-container">

      <section class="left-card">
        <div class="profile-header">
          <img src="img/profile.jpg" alt="Student Image" class="profile-pic">
          <h2>Selamat Datang,<br><span><?php echo strtoupper($row['student_fname']); ?></span></h2>
        </div>

        <div class="info-group">
          <label>NAMA PENUH:</label>
          <input type="text" value="<?php echo strtoupper($row['student_fname']); ?>" readonly>
          <label>NOMBOR MATRIX:</label>
          <input type="text" value="<?php echo strtoupper($row['matrix']); ?>" readonly>
          <label>NOMBOR IC:</label>
          <input type="text" value="<?php echo $row['student_ic']; ?>" readonly>
          <label>JANTINA:</label>
          <input type="text" value="<?php echo strtoupper($row['gender']); ?>" readonly>
          <label>NOMBOR TELEFON:</label>
          <input type="text" value="<?php echo strtoupper($row['contact_num']); ?>" readonly>
          <label>TARIKH LAHIR:</label>
          <input type="text" value="<?php echo date('d M Y', strtotime($row['student_dob'])); ?>" readonly>
          <label>TARIKH MASUK SEKOLAH:</label>
          <input type="text" value="<?php echo date('d M Y', strtotime($row['student_doe'])); ?>" readonly>
        </div>
      </section>

      <section class="right-card">
        <div class="class-info">
          <h3>MAKLUMAT KELAS</h3>
          <label>KELAS:</label>
          <input type="text" value="<?php echo strtoupper($row['class_name']); ?>" readonly>
          <br>
          <hr>
          <h3>MAKLUMAT GURU KELAS</h3>
          <label>GURU KELAS:</label>
          <input type="text" value="<?php echo strtoupper($row['teacher_fname']); ?>" readonly>
          <label>EMEL:</label>
          <input type="text" value="<?php echo $row['teacher_email']; ?>" readonly>
        </div>

        <div class="action-buttons">
          <button class="yellow" onClick="document.location.href='student_dashboard.php';">PAPAN PEMUKA</button>
          <button class="yellow">PETI MASUK</button>
          <form action="logout.php" method="post">
            <button type="submit" class="red">DAFTAR KELUAR</button>
          </form>
        </div>
      </section>

    </main>
  </div>

</body>

</html>