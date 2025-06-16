<?php
include 'connect.php';
session_start();
include 'header.php';

// Notification count logic for teacher and student
$pending_count = 0;
$notif_link = "student_formhistory.php";
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher') {
    $teacher_ic = $_SESSION['user_ic'];
    $sql_class_id = "SELECT class_id FROM class WHERE head_teacher = '$teacher_ic'";
    $result_class_id = mysqli_query($conn, $sql_class_id);
    $teacher_class_id = null;
    if ($result_class_id && mysqli_num_rows($result_class_id) > 0) {
        $row_class_id = mysqli_fetch_assoc($result_class_id);
        $teacher_class_id = $row_class_id['class_id'];
    }
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
    $notif_link = "approve_form.php";
} elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student') {
    $student_ic = $_SESSION['user_ic'] ?? null;
    if ($student_ic) {
        $query = "
          SELECT COUNT(*) AS pending_count 
          FROM cocu_activities 
          WHERE student_ic = ? 
            AND approval_status IN ('pending', 'approved', 'rejected')
            AND notification_read = 0
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $student_ic);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row_pending = $result->fetch_assoc()) {
            $pending_count = $row_pending['pending_count'];
        }
        $stmt->close();
    }
    $notif_link = "student_formhistory.php";
}

// Fetch all existing students to populate dropdowns
$students = [];
$student_query = $conn->query("SELECT student_ic, student_fname FROM student ORDER BY student_ic");
if ($student_query) {
    while ($row = $student_query->fetch_assoc()) {
        $students[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupName = $_POST['group_name'];
    $groupType = $_POST['group_type'];
    $groupDescription = $_POST['group_description'];
    $advisorName = $_POST['advisor_name'];
    $advisorIC = $_POST['advisor_ic'];
    // For roles, get IC or null if empty
    $presidentIC = !empty($_POST['president_ic']) ? $_POST['president_ic'] : null;
    $vicePresidentIC = !empty($_POST['vice_president_ic']) ? $_POST['vice_president_ic'] : null;
    $secretaryIC = !empty($_POST['secretary_ic']) ? $_POST['secretary_ic'] : null;
    $viceSecretaryIC = !empty($_POST['vice_secretary_ic']) ? $_POST['vice_secretary_ic'] : null;
    $treasurerIC = !empty($_POST['treasurer_ic']) ? $_POST['treasurer_ic'] : null;
    $viceTreasurerIC = !empty($_POST['vice_treasurer_ic']) ? $_POST['vice_treasurer_ic'] : null;
    $excoY6IC = !empty($_POST['exco_y6_ic']) ? $_POST['exco_y6_ic'] : null;
    $excoY5IC = !empty($_POST['exco_y5_ic']) ? $_POST['exco_y5_ic'] : null;
    $excoY4IC = !empty($_POST['exco_y4_ic']) ? $_POST['exco_y4_ic'] : null;

    $uploadDir = 'logos/';
    $logoPath = '';
    if (!empty($_FILES['logo']['name'])) {
        $fileName = basename($_FILES['logo']['name']);
        $targetFilePath = $uploadDir . time() . '_' . $fileName;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFilePath)) {
            $logoPath = $targetFilePath;
        }
    }

    $stmt = $conn->prepare("INSERT INTO cocurricular_groups 
        (group_name, group_type, group_description, logo_path, advisor_name, advisor_ic, 
         president_ic, vice_president_ic, secretary_ic, vice_secretary_ic, treasurer_ic, vice_treasurer_ic, exco_y6_ic, exco_y5_ic, exco_y4_ic) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssssssssss",
        $groupName,
        $groupType,
        $groupDescription,
        $logoPath,
        $advisorName,
        $advisorIC,
        $presidentIC,
        $vicePresidentIC,
        $secretaryIC,
        $viceSecretaryIC,
        $treasurerIC,
        $viceTreasurerIC,
        $excoY6IC,
        $excoY5IC,
        $excoY4IC
    );

    if ($stmt->execute()) {
        echo "<script>alert('New group added successfully!'); window.location.href='cocurricular_board.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Kelab - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/add_cocuboard.css" />
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
                  <?php
        // Replace with your actual notification count variable
        $notif_count = $pending_count;
        $notif_link = "student_formhistory.php";
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher') {
            $notif_link = "approve_form.php";
        }
        ?>

        <button onclick="location.href='<?php echo $notif_link; ?>'" style="position: relative; background: none; border: none; cursor: pointer;">
          <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
            notifications
          </span>

          <?php if ($notif_count > 0): ?>
            <span style="
              position: absolute;
              top: -5px;
              right: -5px;
              background: red;
              color: white;
              border-radius: 50%;
              padding: 4px 7px;
              font-size: 12px;
            ">
              <?php echo $notif_count; ?>
            </span>
          <?php endif; ?>
        </button>
        </div>
    </header>

    <div class="container">
        <div class="group-form-container">

            <div class="header">
                <div class="spacer"></div>
                <a class="return-button" href="cocurricular_board.php">KEMBALI</a>
            </div>

            <h2 style="text-align: center;">Tambah Papan Kokurikulum</h2>

            <form method="POST" enctype="multipart/form-data">
                <label>Nama Kumpulan</label>
                <input type="text" name="group_name" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>Kategori Kumpulan</label>
                <div style="margin: 5px 0;">
                    <label><input type="checkbox" name="group_type" value="uniform_bodies" onclick="onlyOne(this)"> Badan Beruniform</label><br>
                    <label><input type="checkbox" name="group_type" value="sports" onclick="onlyOne(this)"> Sukan</label><br>
                    <label><input type="checkbox" name="group_type" value="clubs_associations" onclick="onlyOne(this)"> Kelab</label><br>
                    <label><input type="checkbox" name="group_type" value="others" onclick="onlyOne(this)"> Lain-lain</label>
                </div>

                <label>Maklumat</label>
                <textarea name="group_description" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;"></textarea>

                <label>Nama Penasihat</label>
                <input type="text" name="advisor_name" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <label>IC Penasihat</label>
                <input type="text" name="advisor_ic" required style="width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">

                <!-- Roles dropdowns -->
                <?php
                // Helper function to render a dropdown
                function renderStudentDropdown($name, $students, $label)
                {
                    echo "<label>$label</label>";
                    echo "<select name='$name' style='width:100%; padding:10px; margin:5px 0; border-radius:6px; border:1px solid #ccc;'>";
                    echo "<option value=''>-- Pilih Pelajar (Kosongkan jika tiada) --</option>";
                    foreach ($students as $student) {
                        $fullName = htmlspecialchars($student['student_fname'] . ' ' . $student['student_lname']);
                        $ic = htmlspecialchars($student['student_ic']);
                        echo "<option value='$ic'>$fullName</option>";
                    }
                    echo "</select>";
                }

                renderStudentDropdown('president_ic', $students, 'Presiden');
                renderStudentDropdown('vice_president_ic', $students, 'Naib Presiden');
                renderStudentDropdown('secretary_ic', $students, 'Setiausaha');
                renderStudentDropdown('vice_secretary_ic', $students, 'Naib Setiausaha');
                renderStudentDropdown('treasurer_ic', $students, 'Bendahari');
                renderStudentDropdown('vice_treasurer_ic', $students, 'Naib Bendahari');
                renderStudentDropdown('exco_y6_ic', $students, 'EXCO Tahun 6');
                renderStudentDropdown('exco_y5_ic', $students, 'EXCO Tahun 5');
                renderStudentDropdown('exco_y4_ic', $students, 'EXCO Tahun 4');
                ?>

                <label>Muat Naik Logo (Pilihan)</label>
                <input type="file" name="logo" accept="image/*" style="margin:10px 0;">

                <button type="submit" style="background-color:#28a745; color:white; font-weight:bold; padding:12px 20px; border:none; border-radius:8px; cursor:pointer; width:100%; margin-top:20px;">Tambah Kumpulan</button>
            </form>
        </div>
    </div>

    <script>
        function onlyOne(checkbox) {
            var checkboxes = document.getElementsByName('group_type');
            checkboxes.forEach((item) => {
                if (item !== checkbox) item.checked = false;
            });
        }
    </script>

</body>

</html>