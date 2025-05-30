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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cocu_year = $_POST['cocu_year'];
    $uniform_bodies = $_POST['uniform_bodies'];
    $uniform_bodies_role = $_POST['uniform_bodies_role'];
    $sports = $_POST['sports'];
    $sports_role = $_POST['sports_role'];
    $clubs_assoc = $_POST['clubs_assoc'];
    $clubs_assoc_role = $_POST['clubs_assoc_role'];
    $activity_others = $_POST['activity_others'];

    $sql = "INSERT INTO cocurricular (
        student_ic, cocu_year, uniform_bodies, uniform_bodies_role,
        sports, sports_role, clubs_assoc, clubs_assoc_role, activity_others
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $student_ic, $cocu_year, $uniform_bodies, $uniform_bodies_role,
                      $sports, $sports_role, $clubs_assoc, $clubs_assoc_role, $activity_others);

    if ($stmt->execute()) {
        $success_message = "Data saved successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}

// Get existing cocurricular data (latest entry)
$cocu_query = "SELECT * FROM cocurricular WHERE student_ic = ? ORDER BY cocu_year DESC LIMIT 1";
$stmt = $conn->prepare($cocu_query);

if ($stmt) {
    $stmt->bind_param("s", $student_ic);
    $stmt->execute();
    $cocu_result = $stmt->get_result();
    $existing_cocu = $cocu_result->fetch_assoc();
} else {
    // Optional: Log or display an error message for debugging
    $error_message = "Database error: " . $conn->error;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil Koku Murid - SRIAAWP ActivHub</title>
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
    <h1 class="profile-title">TAMBAH PROFIL KOKURIKULUM</h1>

    <?php if (isset($success_message)) echo "<p style='color:green;'>$success_message</p>"; ?>
    <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>

    <button class="btn-red" onClick="location.href='student_cocurricular.php';">BATAL</button>

    <section class="card-section">
    
        <div class="card right-card">
        <form method="POST" action="">
            <ul class="activity-list">
                <li><label><strong>Tahun: </strong><input type="text" name="cocu_year" value="<?php echo $existing_cocu['cocu_year'] ?? ''; ?>"></label></li>

                <li><label><strong>Unit Beruniform: </strong><input type="text" name="uniform_bodies" value="<?php echo $existing_cocu['uniform_bodies'] ?? ''; ?>"></label></li>
                            
                <li><label><strong>Jawatan: </strong><input type="text" name="uniform_bodies_role" value="<?php echo $existing_cocu['uniform_bodies_role'] ?? ''; ?>"></label></li>
                            
                <li><label><strong>Sukan: </strong><input type="text" name="sports" value="<?php echo $existing_cocu['sports'] ?? ''; ?>"></label></li>
                            
                <li><label><strong>Jawatan: </strong><input type="text" name="sports_role" value="<?php echo $existing_cocu['sports_role'] ?? ''; ?>"></label></li>
                            
                <li><label><strong>Kelab & Persatuan: </strong><input type="text" name="clubs_assoc" value="<?php echo $existing_cocu['clubs_assoc'] ?? ''; ?>"></label></li>
                            
                <li><label><strong>Jawatan: </strong><input type="text" name="clubs_assoc_role" value="<?php echo $existing_cocu['clubs_assoc_role'] ?? ''; ?>"></label></li>
                            
                <li><label><strong>Ekstra Kokurikulum: </strong><input type="text" name="activity_others" value="<?php echo $existing_cocu['activity_others'] ?? ''; ?>"></label></li>
            </ul>
            <button type="submit">Simpan</button>
        </form>
        </div>
    </section>

  

  </div>
</body>
</html>
