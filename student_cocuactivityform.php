<?php
include 'connect.php';
session_start();
include 'header.php';


if (!isset($_SESSION['user_ic']) || !in_array($_SESSION['user_role'], ['student', 'teacher'])) {
  header("Location: login.php?expired=true");
  exit;
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
  $approval_status = ($_SESSION['user_role'] === 'teacher') ? 'approved' : 'pending';

  $sql = "INSERT INTO cocu_activities (
    student_ic, activity_name, activity_category, activity_date,
    award, activity_location, ach, org, cert_path, approval_status
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  if ($_SESSION['user_role'] === 'teacher') {
    $student_ics = $_POST['student_ic']; // This is now an array
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
        $stmt->execute();
        $stmt->close();
    }
    header("Location: viewstudentCocurricular.php?student_ic=" . urlencode($student_ics[0]));
    exit;
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
    } else {
        $error_message = "Error saving activity: " . $stmt->error;
    }
    $stmt->close();
}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Borang Koku Murid - SRIAAWP ActivHub</title>
  <link rel="stylesheet" href="css/header&bg.css" />
  <link rel="stylesheet" href="css/cocurricular.css" />
  <link rel="stylesheet" href="css/button.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
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

    <?php if (isset($success_message)): ?>
      <div id="successModal" class="modal-message">
        <div class="modal-message-content">
          <span class="close-modal" onclick="document.getElementById('successModal').style.display='none'">&times;</span>
          <?php echo $success_message; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>

    <button class="btn-red" onClick="location.href='student_cocurricular.php';">KEMBALI</button>

    <div class="empty-space"></div>
    <div class="act-card activity-form-card">
      <form method="POST" enctype="multipart/form-data">
        <ul class="activity-list">
          <?php if ($_SESSION['user_role'] === 'teacher'): ?>
            <li>
              <label><strong>Pilih Murid:</strong>
                <input type="text" id="studentSearch" placeholder="Cari nama murid..." style="width:100%;margin-bottom:6px;padding:6px 10px;border-radius:6px;border:1px solid #b0b0b0;font-size:1em;">
                <select name="student_ic[]" id="studentDropdown" multiple required size="3" style="height:auto;max-height:120px;overflow-y:auto;font-size:1em;">
                  <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['student_ic']) ?>">
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
          <li><label><strong>Nama Aktiviti:</strong> <input type="text" name="activity_name" required></label></li>
          <li>
            <label>
              <strong>Kategori:</strong>
              <select name="activity_category" id="activity_category_select" required onchange="toggleOtherCategory(this)">
                <option value="">-- Pilih Kategori --</option>
                <option value="Rumah Sukan">Sukan</option>
                <option value="Kelab">Kelab</option>
                <option value="Unit Beruniform">Unit Beruniform</option>
                <option value="Lainnya">Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="activity_category_other" id="activity_category_other" placeholder="Nyatakan kategori lain" style="display:none; margin-top:5px;" />
            </label>
          </li>
          <li><label><strong>Tarikh:</strong> <input type="date" name="activity_date" required></label></li>
          <li>
            <label>
              <strong>Peringkat:</strong>
              <select name="award" id="peringkat_select" required onchange="toggleOtherPeringkat(this)">
                <option value="">-- Pilih Peringkat --</option>
                <option value="Sekolah">Sekolah</option>
                <option value="Daerah">Daerah</option>
                <option value="Negeri">Negeri</option>
                <option value="Kebangsaan">Kebangsaan</option>
                <option value="Antarabangsa">Antarabangsa</option>
                <option value="Lainnya">Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="award_other" id="peringkat_other" placeholder="Nyatakan peringkat lain" style="display:none; margin-top:5px;" />
            </label>
          </li>
          <li><label><strong>Lokasi:</strong> <input type="text" name="activity_location" required></label></li>
          <li><label><strong>Penganjur:</strong> <input type="text" name="org" required></label></li>
          <li>
            <label>
              <strong>Pencapaian:</strong>
              <select name="ach" id="ach_select" required onchange="toggleOtherAch(this)">
                <option value="">-- Pilih Pencapaian --</option>
                <option value="Penyertaan">Penyertaan</option>
                <option value="Johan">Johan</option>
                <option value="Naib Johan">Naib Johan</option>
                <option value="Ketiga">Ketiga</option>
                <option value="Saguhati">Saguhati</option>
                <option value="Lainnya">Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="ach_other" id="ach_other" placeholder="Nyatakan pencapaian lain" style="display:none; margin-top:5px;" />
            </label>
          </li>
          <li><label><strong>Sijil (PDF):</strong> <input type="file" name="cert" accept="application/pdf" required></label></li>
        </ul>
        <div style="text-align: center;">
          <button type="submit" name="submit_competition">Hantar Borang</button>
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