<?php
include 'connect.php';
session_start();
include 'header.php';


// Redirect if not logged in or not a student
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'student') {
  header("Location: login.php?expired=true");
  exit;
}

$student_ic = $_SESSION['user_ic'];

// Get student info
$query = "SELECT s.*, c.class_name, t.teacher_fname, t.teacher_email 
          FROM student s 
          JOIN class c ON s.student_class = c.class_id 
          JOIN teacher t ON s.teacher_incharge = t.teacher_ic 
          WHERE s.student_ic = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_ic);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Handle competition form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_competition'])) {
    $activity_name = $_POST['activity_name'];
    $activity_category = $_POST['activity_category'];
    $activity_date = $_POST['activity_date'];
    $award = $_POST['award'];
    $activity_location = $_POST['activity_location'];
    $org = $_POST['org'];
    $ach = $_POST['ach'];

    // Handle file upload
    $cert = $_FILES['cert'];
    $cert_path = '';

  if ($cert['error'] === UPLOAD_ERR_OK && strtolower(pathinfo($cert['name'], PATHINFO_EXTENSION)) === 'pdf') {
      $upload_dir = 'uploads/certificates/';
      
      // Create folder if it doesn't exist
      if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0777, true);
      }
      
      $cert_path = $upload_dir . basename($cert['name']);
      
      if (move_uploaded_file($cert['tmp_name'], $cert_path)) {
          // Upload success - you can save $cert_path to DB here
      } else {
          echo "Failed to move uploaded file.";
      }
  }

    // Save to DB
    // Save to DB with pending approval status
    $sql = "INSERT INTO cocu_activities (
                student_ic, activity_name, activity_category, activity_date,
                award, activity_location, ach, org, cert_path, approval_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $student_ic, $activity_name, $activity_category, $activity_date,
                  $award, $activity_location, $ach, $org, $cert_path);



    if ($stmt->execute()) {
        $success_message = "Borang berjaya dihantar. Sila tunggu kelulusan guru anda.";
    } else {
        $error_message = "Error saving activity: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Borang Koku Murid - SRIAAWP ActivHub</title>
  <link rel="stylesheet" href="css/profile.css" />
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
    <h1 class="profile-title">BORANG TAMBAH AKTIVITI KOKURIKULUM</h1>

    <?php if (isset($success_message)) echo "<p style='color:green;'>$success_message</p>"; ?>
    <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>

    <button class="btn-red" onClick="location.href='student_cocurricular.php';">BATAL</button>

    <div class="empty-space"></div>
    <div class="act-card activity-form-card">
    <form method="POST" enctype="multipart/form-data">
        <ul class="activity-list">
            <li><label><strong>Nama Aktiviti:</strong> <input type="text" name="activity_name" required></label></li>
            <li><label><strong>Kategori: (Sukan/Kelab/TKRS/Lain-Lain)</strong> <input type="text" name="activity_category" required></label></li>
            <li><label><strong>Tarikh:</strong> <input type="date" name="activity_date" required></label></li>
            <li><label><strong>Peringkat: (Sekolah/Daerah/Negeri/Kebangsaan/Antarabangsa)</strong> <input type="text" name="award"></label></li>
            <li><label><strong>Lokasi:</strong> <input type="text" name="activity_location" required></label></li>
            <li><label><strong>Penganjur:</strong> <input type="text" name="org" required></label></li>
            <li><label><strong>Pencapaian: (Penyertaan/Johan/Lain-Lain)</strong> <input type="text" name="ach" required></label></li>
            <li><label><strong>Sijil (PDF):</strong> <input type="file" name="cert" accept="application/pdf" required></label></li>
        </ul>
        <button type="submit" name="submit_competition">Hantar Borang</button>
    </form>

        </div>
    </section>

  

  </div>
</body>
</html>
