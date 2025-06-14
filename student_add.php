<?php
session_start();
require_once 'connect.php';
include 'header.php';

if (!isset($_SESSION['user_ic'])) {
  header("Location: login.php");
  exit();
}

$user_ic = $_SESSION['user_ic'];

// Get teacher information and their assigned class
$stmt = $conn->prepare("SELECT t.teacher_fname AS name, c.class_id, c.class_name 
                       FROM teacher t 
                       LEFT JOIN class c ON t.teacher_ic = c.head_teacher 
                       WHERE t.teacher_ic = ?");
$stmt->bind_param("s", $user_ic);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
  header("Location: login.php");
  exit();
}

$username = $user_data['name'];
$teacher_class_id = $user_data['class_id'] ?? null;
$teacher_class_name = $user_data['class_name'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate that teacher has a class assigned
  if (!$teacher_class_id) {
    echo "<script>alert('Anda tidak ditugaskan ke mana-mana kelas'); window.location.href='student_dashboard.php';</script>";
    exit();
  }

  $student_ic = $_POST['student_ic'];
  $student_fname = $_POST['student_fname'];
  $gender = $_POST['gender'];
  $matrix = $_POST['matrix'];
  $student_dob = $_POST['student_dob'];
  $student_doe = $_POST['student_doe'];
  $contact_num = $_POST['contact_num'];
  $student_pass = password_hash($_POST['student_pass'], PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO student (
        student_ic, matrix, student_pass, student_fname, student_class,
        gender, student_dob, student_doe, contact_num
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

  $stmt->bind_param(
    "ssssissss",
    $student_ic,
    $matrix,
    $student_pass,
    $student_fname,
    $teacher_class_id,
    $gender,
    $student_dob,
    $student_doe,
    $contact_num
  );

  if ($stmt->execute()) {
    echo "<script>alert('Rekod Berjaya Ditambah'); window.location.href='student_add.php';</script>";
  } else {
    echo "<script>alert('Rekod Tidak Berjaya Ditambah: " . $conn->error . "');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Tambah Pelajar - ActivHub</title>
  <link rel="stylesheet" href="css/profile.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">

</head>

<body>

  <header>
    <div class="logo-section">
      <img src="../img/logo.png" alt="Logo" />
      <div class="logo-text">
        <span>SRI AL-AMIN ActivHub</span>
        <?php include 'navlinks.php'; ?>
      </div>
    </div>

    <div class="icon-section">
      <div class="admin-section">
        <span class="welcome-text">Selamat Datang,<br> <?= htmlspecialchars($username) ?>!</span>
      </div>
      <span class="material-symbols-outlined icon">notifications</span>
    </div>
  </header>

  <div class="container">
    <h1 class="profile-title">TAMBAH REKOD PELAJAR</h1>

    <form method="POST" class="profile-container">
      <section class="left-card">
        <div class="profile-header">
          <img src="img/profile.jpg" alt="Student Image" class="profile-pic">
          <h2>REKOD PELAJAR BAHARU</h2>
        </div>

        <div class="info-group">
          <label class="required-field">NAMA PELAJAR:</label>
          <input type="text" name="student_fname" required>

          <label class="required-field">NO.KAD PENGENALAN:</label>
          <input type="text" name="student_ic" required>

          <label class="required-field">NOMBOR MATRIX:</label>
          <input type="text" name="matrix" required>

          <label class="required-field">KATA LALUAN:</label>
          <div class="password-container">
            <input type="password" name="student_pass" id="student_pass" required>
            <span class="material-symbols-outlined password-toggle" id="togglePassword">visibility</span>
          </div>

          <label class="required-field">JANTINA:</label>
          <select name="gender" required>
            <option value="">-- PILIH JANTINA --</option>
            <option value="Lelaki">LELAKI</option>
            <option value="Perempuan">PEREMPUAN</option>
          </select>

          <label class="required-field">TARIKH LAHIR:</label>
          <input type="date" name="student_dob" required>

          <label class="required-field">TARIKH MASUK:</label>
          <input type="date" name="student_doe" required>

          <label>NOMBOR TELEFON:</label>
          <input type="text" name="contact_num">

          <label>KELAS:</label>
          <?php if ($teacher_class_id): ?>
            <input type="text" value="<?= htmlspecialchars($teacher_class_name) ?>" readonly>
            <input type="hidden" name="student_class" value="<?= $teacher_class_id ?>">
          <?php else: ?>
            <p class="error">Anda tidak ditugaskan ke mana-mana kelas</p>
          <?php endif; ?>
        </div>

        <div class="action-buttons">
          <button class="yellow" type="submit" <?= !$teacher_class_id ? 'disabled' : '' ?>>SIMPAN</button>
          <button class="red" type="reset" onclick="window.location.href='studentList.php'">BATAL</button>
        </div>
      </section>
    </form>
  </div>

</body>

<script>
  const togglePassword = document.querySelector('#togglePassword');
  const password = document.querySelector('#student_pass');

  togglePassword.addEventListener('click', function() {
    // Toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    // Toggle the eye icon
    this.textContent = type === 'password' ? 'visibility' : 'visibility_off';
  });
</script>

</html>