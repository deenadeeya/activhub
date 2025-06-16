<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Prevent session fixation
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

include 'connect.php';
include 'header.php';

// Check authentication and authorization
if (!isset($_SESSION['user_ic']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    echo "<script>alert('Unauthorized access.'); window.location.href='../login.php';</script>";
    exit;
}

$user_ic = $_SESSION['user_ic'];
$user_role = $_SESSION['user_role'];

// Verify head teacher status for teachers
if ($user_role === 'teacher') {
    $headTeacherCheck = mysqli_query($conn, "SELECT class_id FROM class WHERE head_teacher = '$user_ic'");
    if (mysqli_num_rows($headTeacherCheck) === 0) {
        // Show modal instead of alert
        echo '
        <div id="accessDeniedModal" class="modal" style="display:block;">
            <div class="modal-content">
                <h3>Akses Ditolak!</h3>
                <p>Hanya guru kelas boleh mengakses halaman ini.</p>
                <button onclick="redirectToDashboard()">OK</button>
            </div>
        </div>
        <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }
            .modal-content {
                background-color: white;
                margin: 15% auto;
                padding: 20px;
                border-radius: 5px;
                width: 300px;
                text-align: center;
            }
            .modal-content button {
                background-color: #4CAF50;
                color: white;
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 15px;
            }
        </style>
        <script>
            function redirectToDashboard() {
                window.location.href = "../teacher/teacher_dashboard.php";
            }
            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById("accessDeniedModal");
                if (event.target == modal) {
                    redirectToDashboard();
                }
            }
        </script>
        ';
        exit;
    }
}

// Reapprove logic for rejected forms using prepared statements
if (isset($_POST['reapprove']) && isset($_POST['activity_id'])) {
    $activity_id = $_POST['activity_id'];

    // Validate activity_id
    if (!is_numeric($activity_id)) {
        die("Invalid activity ID");
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE cocu_activities SET approval_status = 'approved', approved_by = ?, approved_at = ? WHERE id = ?");
    $stmt->bind_param("ssi", $user_ic, $now, $activity_id);

    if (!$stmt->execute()) {
        die("Error updating record: " . $stmt->error);
    }

    $stmt->close();
    echo "<script>alert('Permohonan berjaya diluluskan semula.'); window.location.href=window.location.href;</script>";
}

// Get teacher's class if they're a head teacher
$teacherClass = null;
$teacher_class_id = null; // <-- Fix: define this variable for later use
if ($user_role === 'teacher') {
    $stmt = $conn->prepare("SELECT class_id FROM class WHERE head_teacher = ?");
    $stmt->bind_param("s", $user_ic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $class = $result->fetch_assoc();
        $teacherClass = $class['class_id'];
        $teacher_class_id = $class['class_id']; // <-- Fix: assign value here
    }
    $stmt->close();
}

// Prepare and execute the appropriate query based on user role
if ($user_role === 'teacher' && $teacherClass) {
    $query = "SELECT a.*, s.student_fname, s.student_class
              FROM cocu_activities a
              JOIN student s ON a.student_ic = s.student_ic
              WHERE s.student_class = ?
              ORDER BY a.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $teacherClass);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($user_role === 'admin') {
    $query = "SELECT a.*, s.student_fname, s.student_class
              FROM cocu_activities a
              JOIN student s ON a.student_ic = s.student_ic
              ORDER BY a.created_at DESC";

    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Database error: " . mysqli_error($conn));
    }
} else {
    die("Akses tanpa kebenaran atau kehilangan maklumat kelas");
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

// Get teacher info for header
$teacher_info = null;
if ($user_role === 'teacher') {
    $stmt = $conn->prepare("SELECT * FROM teacher WHERE teacher_ic = ?");
    $stmt->bind_param("s", $user_ic);
    $stmt->execute();
    $teacher_result = $stmt->get_result();
    if ($teacher_result && $teacher_result->num_rows > 0) {
        $teacher_info = $teacher_result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sejarah Borang Kokurikulum - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="css/header&bg.css" />
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
                    } elseif ($_SESSION['user_role'] === 'teacher' && !empty($teacher_info['teacher_fname'])) {
                        echo '<span class="admin-text">' . strtoupper($teacher_info['teacher_fname']) . '</span><br>';
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
        <h2>Sejarah Permohonan Aktiviti Pelajar</h2>
        <div class="btn-yellow"><a href="<?= $user_role === 'teacher' ? '../teacher/teacher_dashboard.php' : '../admin/admin_dashboard.php' ?>">← Kembali ke Papan Pemuka</a></div>

        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Nama Pelajar</th>
                        <th>Kelas</th>
                        <th>IC</th>
                        <th>Nama Aktiviti</th>
                        <th>Tarikh Aktiviti</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Anugerah</th>
                        <th>Sijil</th>
                        <th>Status</th>
                        <th>Disahkan Oleh</th>
                        <th>Tarikh Disahkan</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_fname']) ?></td>
                            <td><?= htmlspecialchars($row['student_class']) ?></td>
                            <td><?= htmlspecialchars($row['student_ic']) ?></td>
                            <td><?= htmlspecialchars($row['activity_name']) ?></td>
                            <td><?= htmlspecialchars($row['activity_date']) ?></td>
                            <td><?= htmlspecialchars($row['activity_category']) ?></td>
                            <td><?= htmlspecialchars($row['activity_location']) ?></td>
                            <td><?= htmlspecialchars($row['award']) ?></td>
                            <td>
                                <?php if (!empty($row['cert_path'])): ?>
                                    <a href="<?= htmlspecialchars($row['cert_path']) ?>" target="_blank">[Sijil]</a>
                                <?php else: ?>
                                    Tiada
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst($row['approval_status']) ?></td>
                            <td><?= htmlspecialchars($row['approved_by'] ?? '-') ?></td>
                            <td><?= $row['approved_at'] ? date('Y-m-d H:i', strtotime($row['approved_at'])) : '-' ?></td>
                            <td>
                                <?php if (in_array($user_role, ['teacher', 'admin']) && $row['approval_status'] === 'rejected'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="activity_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="reapprove" class="btn-green">Luluskan semula</button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tiada sejarah borang ditemui.</p>
        <?php endif ?>
    </div>
</body>

</html>