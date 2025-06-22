<?php
require_once '../includes/session_check.php';
require_once '../includes/NotificationService.php';
include '../config/connect.php';
include '../includes/header.php';

$success = "";
$error = "";

// Fetch user data based on role
$teacher = null;
$admin_name = null;

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'teacher') {
        // Fetch teacher data
        $teacher_ic = $_SESSION['user_ic'];
        $teacher_query = "SELECT * FROM teacher WHERE teacher_ic = ?";
        $stmt = $conn->prepare($teacher_query);
        $stmt->bind_param("s", $teacher_ic);
        $stmt->execute();
        $teacher_result = $stmt->get_result();
        if ($teacher_result && $teacher_result->num_rows > 0) {
            $teacher = $teacher_result->fetch_assoc();
        }
    } elseif ($_SESSION['user_role'] === 'admin') {
        // For admin, we'll use the session user_ic as the name
        $admin_name = $_SESSION['user_ic'];
    }
}

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
    $event_description = trim($_POST['event_description']);
    $event_type = $_POST['event_type'];
    $is_mandatory = isset($_POST['is_mandatory']) ? 1 : 0;
    $visibility = $_POST['visibility'];
    $max_participants = !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null;
    $registration_deadline = $_POST['registration_deadline'];
    $contact_number = trim($_POST['contact_number']);
    $group_id_input = $_POST['group_id'];
    $eligible_years = isset($_POST['eligible_years']) ? implode(',', $_POST['eligible_years']) : null;

    $group_id = ($group_id_input === 'null') ? null : intval($group_id_input);

    if (empty($event_name) || empty($event_start_date) || empty($event_end_date) || empty($event_venue)) {
        $error = "Sila isi semua ruangan yang wajib.";
    } elseif ($event_start_date > $event_end_date) {
        $error = "Tarikh mula tidak boleh melebihi tarikh tamat.";
    } elseif ($visibility === 'club_only' && !$group_id) {
        $error = "Untuk acara khas ahli kelab, sila pilih kelab/persatuan penganjur.";    } elseif ($visibility === 'private') {
        $error = "Ciri 'Peribadi' masih dalam pembangunan. Sila gunakan 'Ahli Kelab Sahaja' untuk acara terhad.";
    } else {        $stmt = $conn->prepare("UPDATE events SET event_name=?, event_start_date=?, event_end_date=?, event_venue=?, event_description=?, event_type=?, is_mandatory=?, visibility=?, max_participants=?, registration_deadline=?, contact_number=?, group_id=?, eligible_years=? WHERE event_id=?");
        $stmt->bind_param("ssssssisisssii", $event_name, $event_start_date, $event_end_date, $event_venue, $event_description, $event_type, $is_mandatory, $visibility, $max_participants, $registration_deadline, $contact_number, $group_id, $eligible_years, $event_id);if ($stmt->execute()) {
            // 🔥 NEW: Send update notifications to registered participants
            $notificationService = new NotificationService($conn);
            
            // Get all registered students for this event
            $participants_query = "SELECT student_ic FROM event_registrations WHERE event_id = ?";
            $participants_stmt = $conn->prepare($participants_query);
            $participants_stmt->bind_param("i", $event_id);
            $participants_stmt->execute();
            $participants_result = $participants_stmt->get_result();
            
            $notification_title = "Acara Dikemaskini";
            $notification_message = "Acara '{$event_name}' telah dikemaskini. Sila semak butiran terkini.";
            
            while ($participant = $participants_result->fetch_assoc()) {
                $notificationService->createNotification(
                    $participant['student_ic'],
                    'student',
                    'event',
                    $notification_title,
                    $notification_message,
                    $event_id,
                    'events'
                );
            }
            
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
  <title>Kemaskini Acara - SRIAAWP ActivHub</title>  <link rel="stylesheet" href="../assets/css/header&bg.css" />
  <link rel="stylesheet" href="../assets/css/cocurricular.css" />
  <link rel="stylesheet" href="../assets/css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
  <style>
/* Attractive form styling for edit_events.php */
.activity-list select,
.activity-list input[type="text"],
.activity-list input[type="date"],
.activity-list input[type="number"] {
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
.activity-list input[type="date"]:focus,
.activity-list input[type="number"]:focus {
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
</style></head>

<body>
<body>    <header>
    <div class="logo-section">
      <img src="../assets/img/logo.png" alt="Logo" />
      <div class="logo-text">
        <span>SRIAAWP ActivHub</span>
        <?php include '../includes/navlinks.php'; ?>
      </div>
    </div>
    <div class="icon-section">
      <div class="user-section">        <?php
        if (isset($_SESSION['user_role'])) {
            if ($_SESSION['user_role'] === 'admin') {
                echo '<span class="admin-text">' . strtoupper($admin_name ?? $_SESSION['user_ic']) . '</span><br>';
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
            </li>            <li>
              <label><strong>Penerangan Acara:</strong>
                <textarea name="event_description" rows="3" style="width: 100%; padding: 12px 14px; font-size: 1.1rem; border: 1.5px solid #b0b0b0; border-radius: 8px; margin-top: 6px; margin-bottom: 10px; box-sizing: border-box; background: #f8fafc; resize: vertical;" placeholder="Masukkan penerangan ringkas tentang acara ini..."><?= htmlspecialchars($event['event_description']) ?></textarea>
              </label>
            </li>

            <li>
              <label><strong>Jenis Acara:</strong>
                <select name="event_type" required>
                  <option value="other" <?= $event['event_type'] === 'other' ? 'selected' : '' ?>>Lain-lain</option>
                  <option value="meeting" <?= $event['event_type'] === 'meeting' ? 'selected' : '' ?>>Mesyuarat</option>
                  <option value="competition" <?= $event['event_type'] === 'competition' ? 'selected' : '' ?>>Pertandingan</option>
                  <option value="training" <?= $event['event_type'] === 'training' ? 'selected' : '' ?>>Latihan</option>
                  <option value="social" <?= $event['event_type'] === 'social' ? 'selected' : '' ?>>Sosial</option>
                </select>
              </label>
            </li>

            <li>
              <label><strong>Tempat*:</strong>
                <input type="text" name="event_venue" value="<?= htmlspecialchars($event['event_venue']) ?>" required>
              </label>
            </li>

            <li>
              <label><strong>Kelab/Persatuan Penganjur:</strong>
                <br><select name="group_id" id="group_id">
                  <option value="null" <?= is_null($event['group_id']) ? 'selected' : '' ?>>— Acara Bukan Sekolah —</option>
                  <?php foreach ($groups as $group): ?>
                      <option value="<?= $group['group_id'] ?>" <?= ($group['group_id'] == $event['group_id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($group['group_name']) ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </label>
            </li>

            <li id="club_options" style="<?= !is_null($event['group_id']) ? 'display: block;' : 'display: none;' ?>">
              <div style="background: #e6f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc;">
                <p style="margin: 0 0 10px 0; font-weight: bold; color: #0066cc;">📋 Pilihan Kelab/Persatuan:</p>
                
                <label style="display: block; margin-bottom: 8px;">
                  <input type="checkbox" name="is_mandatory" id="is_mandatory" style="margin-right: 8px;" <?= $event['is_mandatory'] ? 'checked' : '' ?>>
                  <strong>Acara ini adalah wajib untuk ahli kelab</strong>
                  <br><small style="color: #666;">⚠️ Tandakan jika kehadiran adalah WAJIB untuk ahli kelab</small>
                </label>
                
                <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-top: 10px; border-left: 3px solid #ffc107;">
                  <small style="color: #856404;">
                    <strong>⚠️ Nota:</strong> Auto-pendaftaran tidak boleh diubah setelah acara dibuat untuk mengelakkan konflik data.
                  </small>
                </div>
              </div>
            </li>

            <li>
              <label><strong>Siapakah yang boleh melihat acara ini?</strong>
                <select name="visibility" id="visibility">
                  <option value="public" <?= $event['visibility'] === 'public' ? 'selected' : '' ?>>🌍 Awam (Semua pelajar boleh lihat dan daftar)</option>
                  <option value="club_only" <?= $event['visibility'] === 'club_only' ? 'selected' : '' ?>>👥 Ahli Kelab Sahaja (Hanya ahli kelab boleh lihat)</option>
                  <option value="private" <?= $event['visibility'] === 'private' ? 'selected' : '' ?> disabled>🔒 Peribadi (COMING SOON)</option>
                </select>
                <small style="display: block; color: #666; margin-top: 4px;">
                  <strong>Nota:</strong> "Peribadi" bermaksud hanya pelajar yang didaftarkan secara manual oleh guru sahaja yang boleh melihat acara ini.
                </small>
              </label>
            </li>

            <li>
              <label><strong>Had Peserta (Opsional):</strong>
                <input type="number" name="max_participants" min="1" value="<?= $event['max_participants'] ?>" placeholder="Tiada had jika kosong">
                <small style="display: block; color: #666; margin-top: 4px;">Kosongkan jika tiada had peserta</small>
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
              <label><strong>Tahun Layak Sertai:</strong><br>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <label style="margin-right: 10px;">
                        <input type="checkbox" name="eligible_years[]" value="<?= $i ?>" <?= in_array((string)$i, $existing_years) ? 'checked' : '' ?>>
                        Tahun <?= $i ?>
                    </label>
                <?php endfor; ?>
              </label>
            </li>
          </ul>          <div class="center-stuff">
            <button type="submit" class="btn-darkblue">Kemaskini Acara</button>
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
            // Uncheck club-specific checkbox
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
        const groupId = document.getElementById('group_id').value;
        const visibility = document.getElementById('visibility').value;
        
        if (visibility === 'club_only' && groupId === 'null') {
            e.preventDefault();
            alert('Untuk acara khas ahli kelab, sila pilih kelab/persatuan penganjur.');
            return false;
        }
    });
  </script>
</body>
</html>