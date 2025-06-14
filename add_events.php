<?php
session_start();
include 'connect.php';
include 'header.php';

$success = "";
$error = "";

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
    $group_id = ($group_id_input === 'null') ? null : intval($group_id_input);
    $eligible_years = isset($_POST['eligible_years']) ? implode(',', $_POST['eligible_years']) : null;

    // Basic validation
    if (empty($event_name) || empty($event_start_date) || empty($event_end_date) || empty($event_venue)) {
        $error = "Sila isi semua ruangan yang wajib.";
    } elseif ($event_start_date > $event_end_date) {
        $error = "Tarikh mula tidak boleh melebihi tarikh tamat.";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (event_name, event_start_date, event_end_date, event_venue, registration_deadline, contact_number, group_id, eligible_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $event_name, $event_start_date, $event_end_date, $event_venue, $registration_deadline, $contact_number, $group_id, $eligible_years);

        if ($stmt->execute()) {
            $success = "Acara berjaya ditambah!";
        } else {
            $error = "Ralat semasa menambah acara: " . $stmt->error;
        }
    }
}

// Get teacher's class id for notification count
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

// Pending approval count
$pending_count = 0;
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
<html lang="ms">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kokurikulum Murid - SRIAAWP ActivHub</title>
  <link rel="stylesheet" href="css/header&bg.css" />
  <link rel="stylesheet" href="css/cocurricular.css" />
  <link rel="stylesheet" href="css/button.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
  <style>
/* Attractive form styling for add_events.php */
.activity-list select,
.activity-list input[type="text"],
.activity-list input[type="date"] {
    width: 100%;
    padding: 12px 14px;
    font-size: 1.1rem;
    border: 1.5px solid #b0b0b0;
    border-radius: 8px;
    margin-top: 6px;
    margin-bottom: 10px;
    box-sizing: border-box;
    background: #f8fafc;
    transition: border 0.2s, box-shadow 0.2s;
}

.activity-list select:focus,
.activity-list input[type="text"]:focus,
.activity-list input[type="date"]:focus {
    border: 1.5px solid #064789;
    box-shadow: 0 0 0 2px #cbd2ff;
    outline: none;
}

.activity-list label strong {
    font-size: 1.08rem;
    color: #064789;
}

.card.event-cocu {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(6,71,137,0.08);
    padding: 32px 28px;
    max-width: 520px;
    margin: 0 auto;
}

.activity-list li {
    margin-bottom: 18px;
    list-style: none;
}

.center-stuff {
    text-align: center;
    margin-top: 18px;
}



@media (max-width: 600px) {
    .card.event-cocu {
        padding: 16px 4px;
        max-width: 98vw;
    }
    .activity-list input, .activity-list select {
        font-size: 1rem;
    }
}
</style>
</head>

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
                <button onclick="location.href='../approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
                    <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
                    notifications
                    </span>
                    <?php if ($pending_count > 0): ?>
                    <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 4px 7px; font-size: 12px;">
                        <?php echo $pending_count; ?>
                    </span>
                    <?php endif; ?>
                </button>
    </div>
  </header>

    <div class="container">
        <h1>TAMBAH ACARA KOKURIKULUM</h1>

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
                        <input type="text" name="event_name" required>
                    </label>
                    </li>

                    <li>
                    <label><strong>Tarikh Mula*:</strong>
                        <br><input type="date" name="event_start_date" required>
                    </label>
                    </li>

                    <li>
                    <label><strong>Tarikh Tamat*:</strong>
                        <br><input type="date" name="event_end_date" required>
                    </label>
                    </li>

                    <li>
                    <label><strong>Tempat*:</strong>
                        <input type="text" name="event_venue" required>
                    </label>
                    </li>

                    <li>
                    <label><strong>Tarikh Akhir Pendaftaran:</strong>
                        <br><input type="date" name="registration_deadline">
                    </label>
                    </li>

                    <li>
                    <label><strong>No Telefon Untuk Dihubungi:</strong>
                        <input type="text" name="contact_number">
                    </label>
                    </li>

                    <li>
                    <label><strong>Tahun Layak Sertai:</strong><br>
                        <?php
                        for ($i = 1; $i <= 6; $i++) {
                            echo "<label style='margin-right: 10px;'><input type='checkbox' name='eligible_years[]' value='{$i}'> Tahun {$i}</label>";
                        }
                        ?>
                    </label>
                    </li>

                    <li>
                    <label><strong>Kelab/Persatuan Penganjur:</strong>
                        <br><select name="group_id">
                        <option value="null">— Acara Bukan Sekolah —</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['group_id'] ?>"><?= htmlspecialchars($group['group_name']) ?></option>
                        <?php endforeach; ?>
                        </select>
                    </label>
                    </li>
                </ul>
                <div class="center-stuff">
                    <button type="submit" class="btn-darkblue">Tambah Acara</button>
                        
                    <?php if ($user_role == 'admin'): ?>
                        <a href="admin/admin_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
                    <?php else: ?>
                        <a href="teacher/teacher_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
                    <?php endif; ?>
                    </form>
                </div>
            </div>
            </section>

    </div>
</body>

</html>