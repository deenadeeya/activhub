<?php
require_once '../includes/session_check.php';
include '../config/connect.php';
include '../includes/header.php';

// Check if user is logged in as teacher
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../auth/login.php?expired=true");
    exit();
}

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
    $event_description = trim($_POST['event_description']);
    $event_type = $_POST['event_type'];
    $is_mandatory = isset($_POST['is_mandatory']) ? 1 : 0;
    $auto_register_members = isset($_POST['auto_register_members']) ? 1 : 0;
    $visibility = $_POST['visibility'];
    $max_participants = !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null;
    $registration_deadline = $_POST['registration_deadline'];
    $contact_number = trim($_POST['contact_number']);
    $group_id_input = $_POST['group_id'];
    $group_id = ($group_id_input === 'null') ? null : intval($group_id_input);
    $eligible_years = isset($_POST['eligible_years']) ? implode(',', $_POST['eligible_years']) : null;
    $created_by = $_SESSION['user_ic'];

    // Basic validation
    if (empty($event_name) || empty($event_start_date) || empty($event_end_date) || empty($event_venue)) {
        $error = "Sila isi semua ruangan yang wajib.";
    } elseif ($event_start_date > $event_end_date) {
        $error = "Tarikh mula tidak boleh melebihi tarikh tamat.";
    } elseif ($auto_register_members && !$group_id) {
        $error = "Untuk auto-pendaftaran ahli, sila pilih kelab/persatuan penganjur.";
    } elseif ($visibility === 'club_only' && !$group_id) {
        $error = "Untuk acara khas ahli kelab, sila pilih kelab/persatuan penganjur.";
    } elseif ($visibility === 'private') {
        $error = "Ciri 'Peribadi' masih dalam pembangunan. Sila gunakan 'Ahli Kelab Sahaja' untuk acara terhad.";
    } else {
        // Insert event
        $stmt = $conn->prepare("INSERT INTO events (event_name, event_start_date, event_end_date, event_venue, event_description, event_type, is_mandatory, auto_register_members, visibility, max_participants, registration_deadline, contact_number, group_id, eligible_years, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiisisisss", $event_name, $event_start_date, $event_end_date, $event_venue, $event_description, $event_type, $is_mandatory, $auto_register_members, $visibility, $max_participants, $registration_deadline, $contact_number, $group_id, $eligible_years, $created_by);

        if ($stmt->execute()) {
            $event_id = $conn->insert_id;
            
            // Auto-register club members if enabled
            if ($auto_register_members && $group_id) {
                $auto_register_query = "
                    INSERT INTO event_registrations (event_id, student_ic, registration_type)
                    SELECT ?, scm.student_ic, 'auto'
                    FROM student_club_membership scm
                    JOIN student s ON scm.student_ic = s.student_ic
                    JOIN class c ON s.student_class = c.class_id
                    WHERE scm.group_id = ?";
                
                // Filter by eligible years if specified
                if ($eligible_years) {
                    $years_array = explode(',', $eligible_years);
                    $years_placeholders = str_repeat('?,', count($years_array) - 1) . '?';
                    $auto_register_query .= " AND c.class_year IN ($years_placeholders)";
                    
                    $auto_stmt = $conn->prepare($auto_register_query);
                    $types = "ii" . str_repeat('i', count($years_array));
                    $params = array_merge([$event_id, $group_id], $years_array);
                    $auto_stmt->bind_param($types, ...$params);
                } else {
                    $auto_stmt = $conn->prepare($auto_register_query);
                    $auto_stmt->bind_param("ii", $event_id, $group_id);
                }
                
                $auto_stmt->execute();
                $auto_registered_count = $auto_stmt->affected_rows;
                $auto_stmt->close();
                
                // Create notifications for auto-registered students
                if ($auto_registered_count > 0) {
                    $notification_query = "
                        INSERT INTO event_notifications (event_id, student_ic, notification_type)
                        SELECT ?, er.student_ic, 'event_created'
                        FROM event_registrations er
                        WHERE er.event_id = ? AND er.registration_type = 'auto'";
                    
                    $notif_stmt = $conn->prepare($notification_query);
                    $notif_stmt->bind_param("ii", $event_id, $event_id);
                    $notif_stmt->execute();
                    $notif_stmt->close();
                }
            }
            
            $success = "Acara berjaya ditambah!";
            if (isset($auto_registered_count) && $auto_registered_count > 0) {
                $success .= " $auto_registered_count ahli kelab telah didaftarkan secara automatik.";
            }
        } else {
            $error = "Ralat semasa menambah acara: " . $stmt->error;
        }
        $stmt->close();
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
  <link rel="stylesheet" href="../assets/css/header&bg.css" />
  <link rel="stylesheet" href="../assets/css/cocurricular.css" />
  <link rel="stylesheet" href="../assets/css/button.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
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
      <img src="../assets/img/logo.png" alt="Logo" />
      <div class="logo-text">
        <span>SRIAAWP ActivHub</span>
        <?php include '../includes/navlinks.php'; ?>
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
                <button onclick="location.href='../forms/approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
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
                    <label><strong>Penerangan Acara:</strong>
                        <textarea name="event_description" rows="3" style="width: 100%; padding: 12px 14px; font-size: 1.1rem; border: 1.5px solid #b0b0b0; border-radius: 8px; margin-top: 6px; margin-bottom: 10px; box-sizing: border-box; background: #f8fafc; resize: vertical;" placeholder="Masukkan penerangan ringkas tentang acara ini..."></textarea>
                    </label>
                    </li>

                    <li>
                    <label><strong>Jenis Acara:</strong>
                        <select name="event_type" required>
                            <option value="other">Lain-lain</option>
                            <option value="meeting">Mesyuarat</option>
                            <option value="competition">Pertandingan</option>
                            <option value="training">Latihan</option>
                            <option value="social">Sosial</option>
                        </select>
                    </label>
                    </li>

                    <li>
                    <label><strong>Kelab/Persatuan Penganjur:</strong>
                        <br><select name="group_id" id="group_id">
                        <option value="null">— Acara Bukan Sekolah —</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['group_id'] ?>"><?= htmlspecialchars($group['group_name']) ?></option>
                        <?php endforeach; ?>
                        </select>
                    </label>
                    </li>

                    <li id="club_options" style="display: none;">
                        <div style="background: #e6f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc;">
                            <p style="margin: 0 0 10px 0; font-weight: bold; color: #0066cc;">📋 Pilihan Kelab/Persatuan:</p>
                            
                            <label style="display: block; margin-bottom: 12px;">
                                <input type="checkbox" name="auto_register_members" id="auto_register_members" style="margin-right: 8px;">
                                <strong>Auto-daftar semua ahli kelab</strong>
                                <br><small style="color: #666;">✅ Semua ahli kelab akan didaftarkan secara automatik tanpa perlu daftar manual</small>
                            </label>
                            
                            <label style="display: block; margin-bottom: 8px;">
                                <input type="checkbox" name="is_mandatory" id="is_mandatory" style="margin-right: 8px;">
                                <strong>Acara ini adalah wajib untuk ahli kelab</strong>
                                <br><small style="color: #666;">⚠️ Tandakan jika kehadiran adalah WAJIB. Ahli akan tetap perlu didaftar secara manual kecuali anda juga tick "Auto-daftar" di atas.</small>
                            </label>
                            
                            <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-top: 10px; border-left: 3px solid #ffc107;">
                                <small style="color: #856404;">
                                    <strong>💡 Tip:</strong> Untuk mesyuarat kelab yang wajib, tick kedua-dua pilihan di atas - ahli akan auto-didaftar dan dimaklumkan acara ini adalah wajib.
                                </small>
                            </div>
                        </div>
                    </li>

                    <li>
                    <label><strong>Siapakah yang boleh melihat acara ini?</strong>
                        <select name="visibility" id="visibility">
                            <option value="public">Awam</option>
                            <option value="club_only">Ahli Kelab Sahaja</option>
                        </select>
                        <small style="display: block; color: #666; margin-top: 4px;">
                            <strong>Nota:</strong> "Peribadi" bermaksud hanya pelajar yang didaftarkan secara manual oleh guru sahaja yang boleh melihat acara ini.
                        </small>
                    </label>
                    </li>

                    <li>
                    <label><strong>Had Peserta:</strong>
                        <input type="number" name="max_participants" min="1" placeholder="Tiada had jika kosong">
                        <small style="display: block; color: #666; margin-top: 4px;">Kosongkan jika tiada had peserta</small>
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
                </ul>
                <div class="center-stuff">
                    <button type="submit" class="btn-darkblue">Tambah Acara</button>
                        
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <a href="../admin/admin_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
                    <?php else: ?>
                        <a href="../teacher/teacher_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
                    <?php endif; ?>
                </div>
                </form>
            </div>
            </section>

    </div>

    <script>
        // Show/hide club-specific options based on group selection
        document.getElementById('group_id').addEventListener('change', function() {
            const groupId = this.value;
            const clubOptions = document.getElementById('club_options');
            const visibilitySelect = document.getElementById('visibility');
            
            if (groupId !== 'null') {
                clubOptions.style.display = 'block';
                // Enable club_only option for visibility
                visibilitySelect.querySelector('option[value="club_only"]').disabled = false;
            } else {
                clubOptions.style.display = 'none';
                // Disable club_only option and reset if selected
                const clubOnlyOption = visibilitySelect.querySelector('option[value="club_only"]');
                clubOnlyOption.disabled = true;
                if (visibilitySelect.value === 'club_only') {
                    visibilitySelect.value = 'public';
                }
                // Uncheck club-specific checkboxes
                document.getElementById('auto_register_members').checked = false;
                document.getElementById('is_mandatory').checked = false;
            }
        });

        // Update visibility options based on group selection
        document.getElementById('visibility').addEventListener('change', function() {
            const visibility = this.value;
            const groupSelect = document.getElementById('group_id');
            
            if (visibility === 'club_only' && groupSelect.value === 'null') {
                alert('Sila pilih kelab/persatuan penganjur untuk acara khas ahli kelab.');
                this.value = 'public';
            }
            
            // Warn about private events (not fully implemented)
            if (visibility === 'private') {
                alert('⚠️ AMARAN: Ciri "Peribadi" masih dalam pembangunan. Untuk masa sekarang, gunakan "Ahli Kelab Sahaja" untuk acara terhad.');
                this.value = 'public';
            }
        });

        // Validate form before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const autoRegister = document.getElementById('auto_register_members').checked;
            const groupId = document.getElementById('group_id').value;
            const visibility = document.getElementById('visibility').value;
            
            if (autoRegister && groupId === 'null') {
                e.preventDefault();
                alert('Untuk auto-pendaftaran ahli, sila pilih kelab/persatuan penganjur.');
                return false;
            }
            
            if (visibility === 'club_only' && groupId === 'null') {
                e.preventDefault();
                alert('Untuk acara khas ahli kelab, sila pilih kelab/persatuan penganjur.');
                return false;
            }
        });
    </script>
</body>

</html>