<?php
include '../config/connect.php';
require_once '../includes/session_check.php';
require_once '../includes/NotificationService.php';
include '../includes/header.php';


if (!isset($_SESSION['user_ic']) || !in_array($_SESSION['user_role'], ['student', 'teacher'])) {
  header("Location: ../auth/login.php?expired=true");
  exit;
}

// Get the target student IC from URL parameter (for teachers)
$target_student_ic = isset($_GET['student_ic']) ? $_GET['student_ic'] : null;

// Handle edit mode
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit_id']) && $_SESSION['user_role'] === 'student') {
    $edit_id = $_GET['edit_id'];
    $student_ic = $_SESSION['user_ic'];
    
    // Fetch the rejected activity data for editing
    $edit_query = "SELECT * FROM cocu_activities WHERE id = ? AND student_ic = ? AND approval_status = 'rejected'";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("is", $edit_id, $student_ic);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    
    if ($edit_result->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $edit_result->fetch_assoc();
    } else {
        echo "<script>alert('Tidak dapat mengedit aktiviti ini.'); window.location.href='student_formhistory.php';</script>";
        exit;
    }
}

// Get student info
$query = "SELECT s.*, c.class_name, t.teacher_fname, t.teacher_email 
          FROM student s 
          JOIN class c ON s.student_class = c.class_id 
          JOIN teacher t ON c.head_teacher = t.teacher_ic 
          WHERE s.student_ic = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_ic);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// For teacher, get all students under the teacher's class
if ($_SESSION['user_role'] === 'teacher') {
    $teacher_ic = $_SESSION['user_ic'];
    $students = [];
    $sql = "SELECT s.student_ic, s.student_fname FROM student s JOIN class c ON s.student_class = c.class_id WHERE c.head_teacher = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $teacher_ic);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// Handle competition form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_competition'])) {
  $activity_name = $_POST['activity_name'];
  $activity_category = $_POST['activity_category'];
  if ($activity_category === 'Lainnya' && !empty($_POST['activity_category_other'])) {
    $activity_category = $_POST['activity_category_other'];
  }
  $activity_date = $_POST['activity_date'];
  $award = $_POST['award'];
  if ($award === 'Lainnya' && !empty($_POST['award_other'])) {
    $ach = $_POST['award_other'];
  }
  $activity_location = $_POST['activity_location'];
  $org = $_POST['org'];
  $ach = $_POST['ach'];
  if ($ach === 'Lainnya' && !empty($_POST['ach_other'])) {
    $ach = $_POST['ach_other'];
  }

  // Handle file upload
  $cert = $_FILES['cert'];
  $cert_path = '';
  
  // If editing and no new file uploaded, keep existing certificate
  if ($edit_mode && (!isset($cert['name']) || empty($cert['name']))) {
      $cert_path = $edit_data['cert_path'];
  } elseif ($cert['error'] === UPLOAD_ERR_OK && strtolower(pathinfo($cert['name'], PATHINFO_EXTENSION)) === 'pdf') {
    $upload_dir = '../assets/uploads/certificates/';

    // Create folder if it doesn't exist
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0777, true);
    }

    $cert_path = 'uploads/certificates/' . basename($cert['name']);
    $full_cert_path = $upload_dir . basename($cert['name']);

    if (move_uploaded_file($cert['tmp_name'], $full_cert_path)) {
      // Upload success - $cert_path now contains the relative path for DB
    } else {
      echo "Failed to move uploaded file.";
    }
  }

  // Save to DB or Update existing record
  if ($edit_mode) {
      // Update existing rejected record
      $sql = "UPDATE cocu_activities SET 
              activity_name = ?, activity_category = ?, activity_date = ?,
              award = ?, activity_location = ?, ach = ?, org = ?, cert_path = ?, 
              approval_status = 'pending', rejection_remarks = NULL, 
              approved_by = NULL, approved_at = NULL, notification_read = 0
              WHERE id = ? AND student_ic = ?";
      
      $stmt = $conn->prepare($sql);
      $stmt->bind_param(
          "ssssssssii",
          $activity_name,
          $activity_category,
          $activity_date,
          $award,
          $activity_location,
          $ach,
          $org,
          $cert_path,
          $edit_data['id'],
          $student_ic
      );
      
      if ($stmt->execute()) {
          $success_message = "Aktiviti berjaya dikemas kini dan dihantar semula untuk kelulusan.";
          
          // Create notification for teacher about resubmission
          $notificationService = new NotificationService($conn);
          
          // Get teacher IC for this student
          $teacher_query = "SELECT c.head_teacher FROM student s JOIN class c ON s.student_class = c.class_id WHERE s.student_ic = ?";
          $teacher_stmt = $conn->prepare($teacher_query);
          $teacher_stmt->bind_param("s", $student_ic);
          $teacher_stmt->execute();
          $teacher_result = $teacher_stmt->get_result();
          
          if ($teacher_data = $teacher_result->fetch_assoc()) {
              $teacher_ic = $teacher_data['head_teacher'];
              $notificationService->notifyTeacherActivitySubmission($teacher_ic, $student_ic, $activity_name, $edit_data['id'], true);
          }
      } else {
          $error_message = "Error updating activity: " . $stmt->error;
      }
      $stmt->close();
  } else {
      // Insert new record
      $approval_status = ($_SESSION['user_role'] === 'teacher') ? 'approved' : 'pending';

      $sql = "INSERT INTO cocu_activities (
        student_ic, activity_name, activity_category, activity_date,
        award, activity_location, ach, org, cert_path, approval_status
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      if ($_SESSION['user_role'] === 'teacher') {
        $student_ics = $_POST['student_ic']; // This is now an array
        $success_count = 0;
        foreach ($student_ics as $student_ic) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssss",
                $student_ic,
                $activity_name,
                $activity_category,
                $activity_date,
                $award,
                $activity_location,
                $ach,
                $org,
                $cert_path,
                $approval_status
            );
            if ($stmt->execute()) {
                $success_count++;
            }
            $stmt->close();
        }
        
        if ($success_count > 0) {
            if (count($student_ics) == 1) {
                $success_message = "Aktiviti berjaya ditambah untuk 1 murid dan telah diluluskan secara automatik.";
                $redirect_student_ic = $target_student_ic ? $target_student_ic : $student_ics[0];
                $redirect_url = "viewstudentCocurricular.php?student_ic=" . urlencode($redirect_student_ic);
            } else {
                $success_message = "Aktiviti berjaya ditambah untuk $success_count murid dan telah diluluskan secara automatik.";
                // If multiple students selected and no target student, redirect to dashboard
                if (!$target_student_ic) {
                    $redirect_url = "../teacher/teacher_dashboard.php";
                } else {
                    $redirect_url = "viewstudentCocurricular.php?student_ic=" . urlencode($target_student_ic);
                }
            }
        } else {
            $error_message = "Ralat berlaku semasa menambah aktiviti. Sila cuba lagi.";
        }
    } else {
        // For students, use their own IC
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssss",
            $student_ic,
            $activity_name,
            $activity_category,
            $activity_date,
            $award,
            $activity_location,
            $ach,
            $org,
            $cert_path,
            $approval_status
        );
        if ($stmt->execute()) {
            $success_message = "Borang berjaya dihantar. Sila tunggu kelulusan guru anda.";
            
            // Create notification for teacher about new submission
            $notificationService = new NotificationService($conn);
            
            // Get teacher IC for this student
            $teacher_query = "SELECT c.head_teacher FROM student s JOIN class c ON s.student_class = c.class_id WHERE s.student_ic = ?";
            $teacher_stmt = $conn->prepare($teacher_query);
            $teacher_stmt->bind_param("s", $student_ic);
            $teacher_stmt->execute();
            $teacher_result = $teacher_stmt->get_result();
            
            if ($teacher_data = $teacher_result->fetch_assoc()) {
                $teacher_ic = $teacher_data['head_teacher'];
                
                // Get the last inserted ID for the notification
                $activity_id = $conn->insert_id;
                $notificationService->notifyTeacherActivitySubmission($teacher_ic, $student_ic, $activity_name, $activity_id, false);
            }
        } else {
            $error_message = "Error saving activity: " . $stmt->error;
        }
        $stmt->close();
    }
  }
}

// Get pending count for notification badge (for teachers)
$pending_count = 0;
if ($_SESSION['user_role'] === 'teacher') {
    $teacher_ic = $_SESSION['user_ic'];
    $teacher_class_id = null;
    $sql_class_id = "SELECT class_id FROM class WHERE head_teacher = '$teacher_ic'";
    $result_class_id = mysqli_query($conn, $sql_class_id);
    if ($result_class_id && mysqli_num_rows($result_class_id) > 0) {
        $row_class_id = mysqli_fetch_assoc($result_class_id);
        $teacher_class_id = $row_class_id['class_id'];
        
        if ($teacher_class_id) {
            $pending_query = "
                SELECT COUNT(*) AS total_pending
                FROM cocu_activities ca
                JOIN student s ON ca.student_ic = s.student_ic
                WHERE ca.approval_status = 'pending' AND s.student_class = ?
            ";
            $stmt_pending = $conn->prepare($pending_query);
            $stmt_pending->bind_param("s", $teacher_class_id);
            $stmt_pending->execute();
            $pending_result = $stmt_pending->get_result();
            $pending_data = $pending_result->fetch_assoc();
            $pending_count = $pending_data['total_pending'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Borang Koku Murid - SRIAAWP ActivHub</title>
  <link rel="stylesheet" href="../assets/css/header&bg.css" />
  <link rel="stylesheet" href="../assets/css/cocurricular.css" />
  <link rel="stylesheet" href="../assets/css/button.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
  <style>
  /* Make dropdowns and inputs larger and more attractive */
  .activity-list select,
  .activity-list input[type="text"],
  .activity-list input[type="date"],
  .activity-list input[type="file"] {
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

  .activity-form-card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 24px rgba(6,71,137,0.08);
      padding: 32px 28px;
      min-width: 600px;
      max-width: 900px;
      margin: 0 auto;
      min-height: 1200px; /* Add this line to increase card height */
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      margin-bottom: 60px;
  }

  .activity-list li {
      margin-bottom: 18px;
  }

  .empty-space {
      height: 18px;
  }

  .activity-list input[type="date"] {
    font-size: 1.2rem;
    height: 48px;
    padding: 10px 14px;
}

.modal-message {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1000; /* Sit on top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

.modal-message-content {
  background-color: #fefefe;
  margin: 15% auto; /* 15% from the top and centered */
  padding: 20px;
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
  max-width: 600px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.close-modal {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close-modal:hover,
.close-modal:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

/* Success Modal Styles */
.modal-message {
  display: block;
  position: fixed;
  z-index: 2000;
  left: 0;
  top: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0,0,0,0.25);
}
.modal-message-content {
  background: #e6ffe6;
  color: #256029;
  border: 2px solid #4caf50;
  border-radius: 10px;
  padding: 32px 24px;
  max-width: 400px;
  margin: 120px auto 0 auto;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
  font-size: 1.15rem;
  text-align: center;
  position: relative;
}
.close-modal {
  position: absolute;
  top: 10px;
  right: 18px;
  font-size: 1.6rem;
  color: #256029;
  cursor: pointer;
  font-weight: bold;
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
      <?php include '../includes/notifications_panel.php'; ?>
    </div>
  </header>

  <div class="container">
    <h1 class="profile-title"><?= $edit_mode ? 'EDIT AKTIVITI KOKURIKULUM' : 'BORANG TAMBAH AKTIVITI KOKURIKULUM' ?></h1>
    
    <?php if ($edit_mode): ?>
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <strong>Sebab Penolakan:</strong> <?= htmlspecialchars($edit_data['rejection_remarks']) ?>
            <br><small>Sila kemaskini maklumat aktiviti dan hantar semula untuk kelulusan.</small>
        </div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
      <div id="successModal" class="modal-message">
        <div class="modal-message-content">
          <span class="close-modal" onclick="closeSuccessModal()">&times;</span>
          <?php echo $success_message; ?>
          <?php if (isset($redirect_url)): ?>
            <div style="margin-top: 15px; font-size: 0.9em; color: #666;">
              <?php if (strpos($redirect_url, 'teacher_dashboard.php') !== false): ?>
                Anda akan dialihkan ke papan pemuka dalam <span id="countdown">3</span> saat...
              <?php else: ?>
                Anda akan dialihkan ke profil murid dalam <span id="countdown">3</span> saat...
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if (isset($redirect_url)): ?>
        <script>
          let countdownTimer = 3;
          const countdownElement = document.getElementById('countdown');
          
          function updateCountdown() {
            countdownElement.textContent = countdownTimer;
            if (countdownTimer <= 0) {
              window.location.href = '<?php echo $redirect_url; ?>';
            } else {
              countdownTimer--;
              setTimeout(updateCountdown, 1000);
            }
          }
          
          function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            window.location.href = '<?php echo $redirect_url; ?>';
          }
          
          // Start countdown
          setTimeout(updateCountdown, 1000);
        </script>
      <?php else: ?>
        <script>
          function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
          }
        </script>
      <?php endif; ?>
    <?php endif; ?>
    <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>

    <?php if ($_SESSION['user_role'] === 'teacher' && $target_student_ic): ?>
        <button class="btn-red" onClick="location.href='viewstudentCocurricular.php?student_ic=<?= urlencode($target_student_ic) ?>';">KEMBALI</button>
    <?php elseif ($_SESSION['user_role'] === 'teacher'): ?>
        <button class="btn-red" onClick="location.href='../teacher/teacher_dashboard.php';">KEMBALI</button>
    <?php else: ?>
        <button class="btn-red" onClick="location.href='student_cocurricular.php';">KEMBALI</button>
    <?php endif; ?>

    <div class="empty-space"></div>
    <div class="act-card activity-form-card">
      <form method="POST" enctype="multipart/form-data">
        <ul class="activity-list">
          <?php if ($_SESSION['user_role'] === 'teacher'): ?>
            <li>
              <label><strong>Pilih Murid:</strong>
                <?php if ($target_student_ic): ?>
                  <div style="background: #e6f3ff; color: #0066cc; padding: 8px 12px; border-radius: 6px; margin-bottom: 8px; font-size: 0.95em;">
                    <strong>📋 Aktiviti untuk murid tertentu:</strong> Murid telah dipra-pilih berdasarkan pandangan yang anda akses.
                  </div>
                <?php endif; ?>
                <input type="text" id="studentSearch" placeholder="Cari nama murid..." style="width:100%;margin-bottom:6px;padding:6px 10px;border-radius:6px;border:1px solid #b0b0b0;font-size:1em;">
                <select name="student_ic[]" id="studentDropdown" multiple required size="3" style="height:auto;max-height:120px;overflow-y:auto;font-size:1em;">
                  <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['student_ic']) ?>" 
                            <?= ($target_student_ic && $target_student_ic === $student['student_ic']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($student['student_fname']) ?> (<?= htmlspecialchars($student['student_ic']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <span style="font-size:0.95em;color:#888;">(Tekan Ctrl atau Shift untuk pilih lebih dari satu murid)</span>
                <div id="selectedStudents" style="margin-top:8px;min-height:24px;color:#064789;font-weight:500;"></div>
              </label>
            </li>
            <script>
              // Simple filter for student dropdown
              document.addEventListener('DOMContentLoaded', function() {
                var search = document.getElementById('studentSearch');
                var dropdown = document.getElementById('studentDropdown');
                var selectedDiv = document.getElementById('selectedStudents');
                function updateSelected() {
                  var selected = Array.from(dropdown.selectedOptions).map(opt => opt.text);
                  if(selected.length > 0) {
                    selectedDiv.innerHTML = "Dipilih: " + selected.join(', ');
                  } else {
                    selectedDiv.innerHTML = "";
                  }
                }
                dropdown.addEventListener('change', updateSelected);
                search.addEventListener('keyup', function() {
                  var filter = search.value.toLowerCase();
                  for (var i = 0; i < dropdown.options.length; i++) {
                    var txt = dropdown.options[i].text.toLowerCase();
                    dropdown.options[i].style.display = txt.includes(filter) ? '' : 'none';
                  }
                });
                // Initial update
                updateSelected();
              });
            </script>
          <?php endif; ?>
          <li><label><strong>Nama Aktiviti:</strong> <input type="text" name="activity_name" value="<?= $edit_mode ? htmlspecialchars($edit_data['activity_name']) : '' ?>" required></label></li>
          <li>
            <label>
              <strong>Kategori:</strong>
              <select name="activity_category" id="activity_category_select" required onchange="toggleOtherCategory(this)">
                <option value="">-- Pilih Kategori --</option>
                <option value="Rumah Sukan" <?= $edit_mode && $edit_data['activity_category'] == 'Rumah Sukan' ? 'selected' : '' ?>>Sukan</option>
                <option value="Kelab" <?= $edit_mode && $edit_data['activity_category'] == 'Kelab' ? 'selected' : '' ?>>Kelab</option>
                <option value="Unit Beruniform" <?= $edit_mode && $edit_data['activity_category'] == 'Unit Beruniform' ? 'selected' : '' ?>>Unit Beruniform</option>
                <option value="Lainnya" <?= $edit_mode && !in_array($edit_data['activity_category'], ['Rumah Sukan', 'Kelab', 'Unit Beruniform']) ? 'selected' : '' ?>>Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="activity_category_other" id="activity_category_other" placeholder="Nyatakan kategori lain" value="<?= $edit_mode && !in_array($edit_data['activity_category'], ['Rumah Sukan', 'Kelab', 'Unit Beruniform']) ? htmlspecialchars($edit_data['activity_category']) : '' ?>" style="<?= $edit_mode && !in_array($edit_data['activity_category'], ['Rumah Sukan', 'Kelab', 'Unit Beruniform']) ? 'margin-top:5px;' : 'display:none; margin-top:5px;' ?>" />
            </label>
          </li>
          <li><label><strong>Tarikh:</strong> <input type="date" name="activity_date" value="<?= $edit_mode ? htmlspecialchars($edit_data['activity_date']) : '' ?>" required></label></li>
          <li>
            <label>
              <strong>Peringkat:</strong>
              <select name="award" id="peringkat_select" required onchange="toggleOtherPeringkat(this)">
                <option value="">-- Pilih Peringkat --</option>
                <option value="Sekolah" <?= $edit_mode && $edit_data['award'] == 'Sekolah' ? 'selected' : '' ?>>Sekolah</option>
                <option value="Daerah" <?= $edit_mode && $edit_data['award'] == 'Daerah' ? 'selected' : '' ?>>Daerah</option>
                <option value="Negeri" <?= $edit_mode && $edit_data['award'] == 'Negeri' ? 'selected' : '' ?>>Negeri</option>
                <option value="Kebangsaan" <?= $edit_mode && $edit_data['award'] == 'Kebangsaan' ? 'selected' : '' ?>>Kebangsaan</option>
                <option value="Antarabangsa" <?= $edit_mode && $edit_data['award'] == 'Antarabangsa' ? 'selected' : '' ?>>Antarabangsa</option>
                <option value="Lainnya" <?= $edit_mode && !in_array($edit_data['award'], ['Sekolah', 'Daerah', 'Negeri', 'Kebangsaan', 'Antarabangsa']) ? 'selected' : '' ?>>Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="award_other" id="peringkat_other" placeholder="Nyatakan peringkat lain" value="<?= $edit_mode && !in_array($edit_data['award'], ['Sekolah', 'Daerah', 'Negeri', 'Kebangsaan', 'Antarabangsa']) ? htmlspecialchars($edit_data['award']) : '' ?>" style="<?= $edit_mode && !in_array($edit_data['award'], ['Sekolah', 'Daerah', 'Negeri', 'Kebangsaan', 'Antarabangsa']) ? 'margin-top:5px;' : 'display:none; margin-top:5px;' ?>" />
            </label>
          </li>
          <li><label><strong>Lokasi:</strong> <input type="text" name="activity_location" value="<?= $edit_mode ? htmlspecialchars($edit_data['activity_location']) : '' ?>" required></label></li>
          <li><label><strong>Penganjur:</strong> <input type="text" name="org" value="<?= $edit_mode ? htmlspecialchars($edit_data['org']) : '' ?>" required></label></li>
          <li>
            <label>
              <strong>Pencapaian:</strong>
              <select name="ach" id="ach_select" required onchange="toggleOtherAch(this)">
                <option value="">-- Pilih Pencapaian --</option>
                <option value="Penyertaan" <?= $edit_mode && $edit_data['ach'] == 'Penyertaan' ? 'selected' : '' ?>>Penyertaan</option>
                <option value="Johan" <?= $edit_mode && $edit_data['ach'] == 'Johan' ? 'selected' : '' ?>>Johan</option>
                <option value="Naib Johan" <?= $edit_mode && $edit_data['ach'] == 'Naib Johan' ? 'selected' : '' ?>>Naib Johan</option>
                <option value="Ketiga" <?= $edit_mode && $edit_data['ach'] == 'Ketiga' ? 'selected' : '' ?>>Ketiga</option>
                <option value="Saguhati" <?= $edit_mode && $edit_data['ach'] == 'Saguhati' ? 'selected' : '' ?>>Saguhati</option>
                <option value="Lainnya" <?= $edit_mode && !in_array($edit_data['ach'], ['Penyertaan', 'Johan', 'Naib Johan', 'Ketiga', 'Saguhati']) ? 'selected' : '' ?>>Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="ach_other" id="ach_other" placeholder="Nyatakan pencapaian lain" value="<?= $edit_mode && !in_array($edit_data['ach'], ['Penyertaan', 'Johan', 'Naib Johan', 'Ketiga', 'Saguhati']) ? htmlspecialchars($edit_data['ach']) : '' ?>" style="<?= $edit_mode && !in_array($edit_data['ach'], ['Penyertaan', 'Johan', 'Naib Johan', 'Ketiga', 'Saguhati']) ? 'margin-top:5px;' : 'display:none; margin-top:5px;' ?>" />
            </label>
          </li>
          <li>
            <label><strong>Sijil (PDF):</strong> 
              <input type="file" name="cert" accept="application/pdf" <?= $edit_mode ? '' : 'required' ?>>
              <?php if ($edit_mode && !empty($edit_data['cert_path'])): ?>
                <br><small style="color: #666;">Sijil semasa: <a href="../assets/uploads/certificates/<?= htmlspecialchars(basename($edit_data['cert_path'])) ?>" target="_blank"><?= htmlspecialchars(basename($edit_data['cert_path'])) ?></a></small>
                <br><small style="color: #888;">Tinggalkan kosong jika tidak mahu menukar sijil.</small>
              <?php endif; ?>
            </label>
          </li>
        </ul>
        <div style="text-align: center;">
          <button type="submit" name="submit_competition"><?= $edit_mode ? 'Kemaskini & Hantar Semula' : 'Hantar Borang' ?></button>
          <?php if ($edit_mode): ?>
            <a href="student_formhistory.php" style="margin-left: 15px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Batal</a>
          <?php endif; ?>
        </div>
      </form>

    </div>
    </section>



  </div>

  <script>
    function toggleOtherCategory(select) {
      var otherInput = document.getElementById('activity_category_other');
      if (select.value === 'Lainnya') {
        otherInput.style.display = 'inline-block';
        otherInput.required = true;
      } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
      }
    }
    function toggleOtherPeringkat(select) {
      var otherInput = document.getElementById('peringkat_other');
      if (select.value === 'Lainnya') {
        otherInput.style.display = 'inline-block';
        otherInput.required = true;
      } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
      }
    }
    function toggleOtherAch(select) {
      var otherInput = document.getElementById('ach_other');
      if (select.value === 'Lainnya') {
        otherInput.style.display = 'inline-block';
        otherInput.required = true;
      } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
      }
    }
  </script>
</body>

</html>