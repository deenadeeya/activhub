<?php
session_start();
include 'connect.php';
include 'header.php';

// Access control logic
$student_ic = null;
if (!isset($_SESSION['user_ic']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php?expired=true");
    exit;
}

if ($_SESSION['user_role'] === 'student') {
    $student_ic = $_SESSION['user_ic'];
} elseif ($_SESSION['user_role'] === 'teacher' && isset($_GET['student_ic'])) {
    // Check if the student is in the teacher's class
    $teacher_ic = $_SESSION['user_ic'];
    $student_ic = $_GET['student_ic'];
    $query = "SELECT s.student_ic FROM student s JOIN class c ON s.student_class = c.class_id WHERE s.student_ic = ? AND c.head_teacher = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $student_ic, $teacher_ic);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Not allowed
        echo "<script>alert('Akses ditolak. Anda hanya boleh melihat murid dalam kelas anda.');window.location.href='studentList.php';</script>";
        exit;
    }
} else {
    // Not allowed
    header("Location: login.php?expired=true");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: viewstudentCocurricular.php");
    exit;
}

$id = intval($_GET['id']);
$success_message = "";
$error_message = "";

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM cocu_activities WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$activity = $result->fetch_assoc();
$stmt->close();

if (!$activity) {
    header("Location: viewstudentCocurricular.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_competition'])) {
    // Handle "Lainnya" for category, award, ach
    $activity_category = ($_POST['activity_category'] === 'Lainnya' && !empty($_POST['activity_category_other']))
        ? $_POST['activity_category_other']
        : $_POST['activity_category'];
    $award = ($_POST['award'] === 'Lainnya' && !empty($_POST['award_other']))
        ? $_POST['award_other']
        : $_POST['award'];
    $ach = ($_POST['ach'] === 'Lainnya' && !empty($_POST['ach_other']))
        ? $_POST['ach_other']
        : $_POST['ach'];

    $activity_name = trim($_POST['activity_name']);
    $activity_date = $_POST['activity_date'];
    $activity_location = trim($_POST['activity_location']);
    $org = trim($_POST['org']);

    // Handle file upload (optional: only update if a new file is uploaded)
    $cert_path = $activity['cert_path'];
    if (isset($_FILES['cert']) && $_FILES['cert']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $filename = uniqid() . '_' . basename($_FILES['cert']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['cert']['tmp_name'], $target_file)) {
            $cert_path = $target_file;
        }
    }

    $stmt = $conn->prepare("UPDATE cocu_activities SET activity_name=?, activity_category=?, activity_date=?, award=?, activity_location=?, ach=?, org=?, cert_path=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $activity_name, $activity_category, $activity_date, $award, $activity_location, $ach, $org, $cert_path, $id);

    if ($stmt->execute()) {
        $success_message = "Aktiviti berjaya dikemaskini.";
        // Refresh data
        $activity['activity_name'] = $activity_name;
        $activity['activity_category'] = $activity_category;
        $activity['activity_date'] = $activity_date;
        $activity['award'] = $award;
        $activity['activity_location'] = $activity_location;
        $activity['ach'] = $ach;
        $activity['org'] = $org;
        $activity['cert_path'] = $cert_path;
    } else {
        $error_message = "Ralat semasa mengemaskini aktiviti.";
    }
    $stmt->close();
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Aktiviti - SRIAAWP ActivHub</title>
  <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../css/cocurricular.css" />
  <link rel="stylesheet" href="../css/dash.css" />
  <link rel="stylesheet" href="../css/header&bg.css" />
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
      margin: 0 auto;
      min-height: 1000px; /* Add this line to increase card height */
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
        <span class="admin-text"><?php echo strtoupper($teacher['teacher_fname']); ?></span><br>
        <span class="welcome-text">Selamat Kembali!</span>
      </div>
      <button onclick="location.href='approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
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
    <h1 class="profile-title">BORANG KEMASKINI AKTIVITI KOKURIKULUM</h1>

    <?php if ($success_message): ?>
      <div id="successModal" class="modal-message">
        <div class="modal-message-content">
          <span class="close-modal" onclick="document.getElementById('successModal').style.display='none'">&times;</span>
          <?php echo $success_message; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($error_message) echo "<p style='color:red;'>$error_message</p>"; ?>

    <button class="btn-red" onClick="location.href='viewstudentCocurricular.php';">KEMBALI</button>

    <div class="empty-space"></div>
    <div class="act-card activity-form-card">
      <form method="POST" enctype="multipart/form-data">
        <ul class="activity-list">
          <li>
            <label><strong>Nama Aktiviti:</strong>
              <input type="text" name="activity_name" value="<?= htmlspecialchars($activity['activity_name']) ?>" required>
            </label>
          </li>
          <li>
            <label>
              <strong>Kategori:</strong>
              <select name="activity_category" id="activity_category_select" required onchange="toggleOtherCategory(this)">
                <option value="">-- Pilih Kategori --</option>
                <option value="Rumah Sukan" <?= $activity['activity_category']=='Rumah Sukan'?'selected':''; ?>>Sukan</option>
                <option value="Kelab" <?= $activity['activity_category']=='Kelab'?'selected':''; ?>>Kelab</option>
                <option value="Unit Beruniform" <?= $activity['activity_category']=='Unit Beruniform'?'selected':''; ?>>Unit Beruniform</option>
                <option value="Lainnya" <?= (!in_array($activity['activity_category'], ['Rumah Sukan','Kelab','Unit Beruniform']) && !empty($activity['activity_category']))?'selected':''; ?>>Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="activity_category_other" id="activity_category_other"
                placeholder="Nyatakan kategori lain"
                style="display:<?= (!in_array($activity['activity_category'], ['Rumah Sukan','Kelab','Unit Beruniform']) && !empty($activity['activity_category']))?'inline-block':'none'; ?>; margin-top:5px;"
                value="<?= (!in_array($activity['activity_category'], ['Rumah Sukan','Kelab','Unit Beruniform'])) ? htmlspecialchars($activity['activity_category']) : '' ?>">
            </label>
          </li>
          <li>
            <label><strong>Tarikh:</strong>
              <input type="date" name="activity_date" value="<?= htmlspecialchars($activity['activity_date']) ?>" required>
            </label>
          </li>
          <li>
            <label>
              <strong>Peringkat:</strong>
              <select name="award" id="peringkat_select" required onchange="toggleOtherPeringkat(this)">
                <option value="">-- Pilih Peringkat --</option>
                <option value="Sekolah" <?= $activity['award']=='Sekolah'?'selected':''; ?>>Sekolah</option>
                <option value="Daerah" <?= $activity['award']=='Daerah'?'selected':''; ?>>Daerah</option>
                <option value="Negeri" <?= $activity['award']=='Negeri'?'selected':''; ?>>Negeri</option>
                <option value="Kebangsaan" <?= $activity['award']=='Kebangsaan'?'selected':''; ?>>Kebangsaan</option>
                <option value="Antarabangsa" <?= $activity['award']=='Antarabangsa'?'selected':''; ?>>Antarabangsa</option>
                <option value="Lainnya" <?= (!in_array($activity['award'], ['Sekolah','Daerah','Negeri','Kebangsaan','Antarabangsa']) && !empty($activity['award']))?'selected':''; ?>>Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="award_other" id="peringkat_other"
                placeholder="Nyatakan peringkat lain"
                style="display:<?= (!in_array($activity['award'], ['Sekolah','Daerah','Negeri','Kebangsaan','Antarabangsa']) && !empty($activity['award']))?'inline-block':'none'; ?>; margin-top:5px;"
                value="<?= (!in_array($activity['award'], ['Sekolah','Daerah','Negeri','Kebangsaan','Antarabangsa'])) ? htmlspecialchars($activity['award']) : '' ?>">
            </label>
          </li>
          <li>
            <label><strong>Lokasi:</strong>
              <input type="text" name="activity_location" value="<?= htmlspecialchars($activity['activity_location']) ?>" required>
            </label>
          </li>
          <li>
            <label><strong>Penganjur:</strong>
              <input type="text" name="org" value="<?= htmlspecialchars($activity['org']) ?>" required>
            </label>
          </li>
          <li>
            <label>
              <strong>Pencapaian:</strong>
              <select name="ach" id="ach_select" required onchange="toggleOtherAch(this)">
                <option value="">-- Pilih Pencapaian --</option>
                <option value="Penyertaan" <?= $activity['ach']=='Penyertaan'?'selected':''; ?>>Penyertaan</option>
                <option value="Johan" <?= $activity['ach']=='Johan'?'selected':''; ?>>Johan</option>
                <option value="Naib Johan" <?= $activity['ach']=='Naib Johan'?'selected':''; ?>>Naib Johan</option>
                <option value="Ketiga" <?= $activity['ach']=='Ketiga'?'selected':''; ?>>Ketiga</option>
                <option value="Saguhati" <?= $activity['ach']=='Saguhati'?'selected':''; ?>>Saguhati</option>
                <option value="Lainnya" <?= (!in_array($activity['ach'], ['Penyertaan','Johan','Naib Johan','Ketiga','Saguhati']) && !empty($activity['ach']))?'selected':''; ?>>Lain-Lain (Nyatakan)</option>
              </select>
              <input type="text" name="ach_other" id="ach_other"
                placeholder="Nyatakan pencapaian lain"
                style="display:<?= (!in_array($activity['ach'], ['Penyertaan','Johan','Naib Johan','Ketiga','Saguhati']) && !empty($activity['ach']))?'inline-block':'none'; ?>; margin-top:5px;"
                value="<?= (!in_array($activity['ach'], ['Penyertaan','Johan','Naib Johan','Ketiga','Saguhati'])) ? htmlspecialchars($activity['ach']) : '' ?>">
            </label>
          </li>
          <li>
            <label><strong>Sijil (PDF):</strong>
              <input type="file" name="cert" accept="application/pdf">
              <?php if (!empty($activity['cert_path'])): ?>
                <br><a href="<?= htmlspecialchars($activity['cert_path']) ?>" target="_blank" style="color:#064789;">[Lihat Sijil]</a>
              <?php endif; ?>
            </label>
          </li>
        </ul>
        <div style="text-align: center;">
          <button type="submit" name="submit_competition">Simpan</button>
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
  </div>
</body>
</html>