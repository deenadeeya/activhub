<?php
session_start();
include 'connect.php';
include 'header.php';

$success = "";
$error = "";

// Check if event_id is provided
if (!isset($_GET['event_id'])) {
    die("ID acara tidak diberikan.");
}

$event_id = intval($_GET['event_id']);

// Fetch existing event data
$event_query = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$event_query->bind_param("i", $event_id);
$event_query->execute();
$event_result = $event_query->get_result();

if ($event_result->num_rows !== 1) {
    die("Acara tidak dijumpai.");
}

$event = $event_result->fetch_assoc();
$existing_years = explode(',', $event['eligible_years']);

// Fetch cocurricular groups
$groups = [];
$group_query = "SELECT group_id, group_name FROM cocurricular_groups ORDER BY group_name ASC";
$group_result = mysqli_query($conn, $group_query);

if ($group_result && mysqli_num_rows($group_result) > 0) {
    while ($row = mysqli_fetch_assoc($group_result)) {
        $groups[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = trim($_POST['event_name']);
    $event_start_date = $_POST['event_start_date'];
    $event_end_date = $_POST['event_end_date'];
    $event_venue = trim($_POST['event_venue']);
    $registration_deadline = $_POST['registration_deadline'];
    $contact_number = trim($_POST['contact_number']);
    $group_id_input = $_POST['group_id'];
    $eligible_years = isset($_POST['eligible_years']) ? implode(',', $_POST['eligible_years']) : null;

    $group_id = ($group_id_input === 'null') ? null : intval($group_id_input);

    if (empty($event_name) || empty($event_start_date) || empty($event_end_date) || empty($event_venue)) {
        $error = "Sila isi semua ruangan yang wajib.";
    } elseif ($event_start_date > $event_end_date) {
        $error = "Tarikh mula tidak boleh melebihi tarikh tamat.";
    } else {
        $stmt = $conn->prepare("UPDATE events SET event_name=?, event_start_date=?, event_end_date=?, event_venue=?, registration_deadline=?, contact_number=?, group_id=?, eligible_years=? WHERE event_id=?");
        $stmt->bind_param("ssssssisi", $event_name, $event_start_date, $event_end_date, $event_venue, $registration_deadline, $contact_number, $group_id, $eligible_years, $event_id);

        if ($stmt->execute()) {
            $success = "Acara berjaya dikemaskini!";
        } else {
            $error = "Ralat semasa mengemaskini acara: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kemaskini Acara - SRIAAWP ActivHub</title>
  <link rel="stylesheet" href="css/header&bg.css" />
  <link rel="stylesheet" href="css/cocurricular.css" />
  <link rel="stylesheet" href="css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">

  
</head>

<body>
<body>
    <header>
    <div class="logo-section">
      <img src="../img/logo.png" alt="Logo" />
      <div class="logo-text">
        <span>SRIAAWP ActivHub</span>
        <div class="nav-links">
            <?php if ($user_role === 'admin'): ?>
                <a href="../admin/admin_dashboard.php">Papan Pemuka</a>
                <a href="../admin/admin_list.php">Senarai Admin</a>
                <a href="#">Senarai Guru-Guru</a>
                <a href="../admin/admin_studentList.php">Senarai Murid-Murid</a>
            <?php elseif ($user_role === 'teacher'): ?>
                <a href="../teacher/teacher_dashboard.php">Papan Pemuka</a>
                <a href="../audit_history.php">Sejarah Borang</a>
                <a href="../teacher/teacher_profile.php">Profil Guru</a>
                <a href="../studentList.php">Senarai Pelajar</a>
                <a href="../approve_form.php">Senarai Borang</a>
                <a href="../add_events.php">Tambah Acara Kokurikulum</a>
                <a href="../cocurricular_board.php">Papan Kokurikulum</a>
            <?php elseif ($user_role === 'student'): ?>
                <a href="student_dashboard.php">Papan Pemuka</a>
                <a href="student_formhistory.php">Sejarah Borang</a>
                <a href="student_profile.php">Profil Murid</a>
                <a href="student_cocurricular.php">Profil & Aktiviti Kokurikulum</a>
                <a href="cocurricular_board.php">Papan Kokurikulum</a>
            <?php else: ?>
                <a href="index.php">Laman Utama</a>
            <?php endif; ?>
        </div>
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
    <h1>KEMASKINI ACARA</h1>

    <?php if ($success): ?>
        <p style="color: green; font-weight: bold;"><?= $success ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: red; font-weight: bold;"><?= $error ?></p>
    <?php endif; ?>

    <section class="card-section">
      <div class="card event-cocu">
        <form method="POST" action="">
          <ul class="activity-list">
            <li>
              <label><strong>Nama Acara*:</strong>
                <input type="text" name="event_name" value="<?= htmlspecialchars($event['event_name']) ?>" required>
              </label>
            </li>

            <li>
              <label><strong>Tarikh Mula*:</strong>
                <br><input type="date" name="event_start_date" value="<?= $event['event_start_date'] ?>" required>
              </label>
            </li>

            <li>
              <label><strong>Tarikh Tamat*:</strong>
                <br><input type="date" name="event_end_date" value="<?= $event['event_end_date'] ?>" required>
              </label>
            </li>

            <li>
              <label><strong>Tempat*:</strong>
                <input type="text" name="event_venue" value="<?= htmlspecialchars($event['event_venue']) ?>" required>
              </label>
            </li>

            <li>
              <label><strong>Tarikh Akhir Pendaftaran:</strong>
                <br><input type="date" name="registration_deadline" value="<?= $event['registration_deadline'] ?>">
              </label>
            </li>

            <li>
              <label><strong>No Telefon Untuk Dihubungi:</strong>
                <input type="text" name="contact_number" value="<?= htmlspecialchars($event['contact_number']) ?>">
              </label>
            </li>

            <li>
              <label><strong>Kelab/Persatuan Penganjur:</strong>
                <br><select name="group_id">
                  <option value="null" <?= is_null($event['group_id']) ? 'selected' : '' ?>>— Acara Bukan Sekolah —</option>
                  <?php foreach ($groups as $group): ?>
                      <option value="<?= $group['group_id'] ?>" <?= ($group['group_id'] == $event['group_id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($group['group_name']) ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </label>
            </li>

            <li>
              <label><strong>Tahun Layak Sertai:</strong><br>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <label style="margin-right: 10px;">
                        <input type="checkbox" name="eligible_years[]" value="<?= $i ?>" <?= in_array((string)$i, $existing_years) ? 'checked' : '' ?>>
                        Tahun <?= $i ?>
                    </label>
                <?php endfor; ?>
              </label>
            </li>
          </ul>

          <div class="center-stuff">
            <button type="submit" class="btn-darkblue">Kemaskini Acara</button>
            <?php if ($user_role == 'admin'): ?>
              <a href="admin/admin_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
            <?php else: ?>
              <a href="teacher/teacher_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </section>
  </div>
</body>
</html>