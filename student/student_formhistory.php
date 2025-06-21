<?php
require_once '../includes/session_check.php';
include '../config/connect.php';
include '../includes/header.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'student') {
    echo "Akses ditolak. Sila <a href='../auth/login.php'>log masuk</a>.";
    exit;
}

$student_ic = $_SESSION['user_ic'];
$history_query = "
    SELECT a.*, s.student_fname, s.student_class, t.teacher_fname AS approver_name
    FROM cocu_activities a
    JOIN student s ON a.student_ic = s.student_ic
    LEFT JOIN teacher t ON a.approved_by = t.teacher_ic
    WHERE a.student_ic = '$student_ic'
    ORDER BY a.created_at DESC
";

$history_result = mysqli_query($conn, $history_query);

// Pending Notification
$notif_query = "
  SELECT COUNT(*) AS pending_count 
  FROM cocu_activities 
  WHERE student_ic = '$student_ic' 
    AND approval_status IN ('pending', 'approved', 'rejected')
    AND notification_read = 0
";

$notif_result = mysqli_query($conn, $notif_query);
$pending_count = 0;

if ($notif_result && $row_pending = mysqli_fetch_assoc($notif_result)) {
  $pending_count = $row_pending['pending_count'];
}

// Mark notifications as read for this student
$update_query = "
  UPDATE cocu_activities
  SET notification_read = 1
  WHERE student_ic = '$student_ic' 
    AND approval_status IN ('pending', 'approved', 'rejected') 
    AND notification_read = 0
";
mysqli_query($conn, $update_query);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Sejarah Permohonan Ko-kurikulum</title>
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link rel="stylesheet" href="../assets/css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
    /* Stylish table for history */
    .history-table {
        width: 80%;
        border-collapse: collapse;
        margin: 24px 0;
        font-size: 1rem;
        font-family: Arial, sans-serif;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.07);
        background: #fff;
    }
    .history-table thead tr {
        background-color: #064789;
        color: #fff;
        text-align: left;
    }
    .history-table th, .history-table td {
        padding: 12px 15px;
        border: 1px solid #e0e0e0;
    }
    .history-table tbody tr {
        background-color: #f9f9f9;
    }
    .history-table tbody tr:nth-child(even) {
        background-color: #f1f6fa;
    }
    .history-table tbody tr:hover {
        background-color: #e3eefd;
    }
    .history-table a {
        color: #064789;
        text-decoration: underline;
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
      <?php
        // Replace with your actual notification count variable
        $notif_count = $pending_count;
        ?>

        <button onclick="location.href='student_formhistory.php'" style="position: relative; background: none; border: none; cursor: pointer;">
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
    <h1>SEJARAH PERMOHONAN AKTIVITI ANDA</h1>
    <div class="btn-yellow" ><a href="student_dashboard.php">← Kembali ke Papan Pemuka</a></div>

    <?php if (mysqli_num_rows($history_result) > 0): ?>
        <table class="history-table" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                <th>Tarikh</th>
                <th>Nama Aktiviti</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Peringkat</th>
                <th>Pencapaian</th>
                <th>Sijil</th>
                <th>Status</th>
                <th>Disahkan Oleh</th>
                <th>Tarikh Disahkan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($history_result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                    <tr>
                    <td><?= htmlspecialchars($row['activity_date']) ?></td>
                    <td><?= htmlspecialchars($row['activity_name']) ?></td>
                    <td><?= htmlspecialchars($row['activity_category']) ?></td>
                    <td><?= htmlspecialchars($row['activity_location']) ?></td>
                    <td><?= htmlspecialchars($row['award']) ?></td>
                    <td><?= htmlspecialchars($row['ach']) ?></td>
                    <td>
                        <?php if (!empty($row['cert_path'])): ?>
                            <?php 
                            // Handle certificate path - files are in assets/uploads/certificates/
                            $cert_path = $row['cert_path'];
                            $filename = basename($cert_path);
                            
                            // Try different possible paths including certificates subfolder
                            $possible_paths = [
                                '../assets/uploads/certificates/' . $filename,
                                '../assets/uploads/' . $filename,
                                '../assets/img/uploads/' . $filename,
                                '../uploads/certificates/' . $filename,
                                '../uploads/' . $filename,
                                $cert_path // original path as fallback
                            ];
                            
                            $found_path = null;
                            foreach ($possible_paths as $test_path) {
                                // For file existence check, convert relative path to absolute
                                if (strpos($test_path, '../') === 0) {
                                    $absolute_path = __DIR__ . '/' . $test_path;
                                } else {
                                    $absolute_path = __DIR__ . '/../' . $test_path;
                                }
                                
                                if (file_exists($absolute_path)) {
                                    $found_path = $test_path;
                                    break;
                                }
                            }
                            
                            if ($found_path): ?>
                                <a style="color: #064789;" href="<?= htmlspecialchars($found_path) ?>" target="_blank">[Sijil]</a>
                            <?php else: ?>
                                <!-- Default to certificates folder -->
                                <a style="color: #064789;" href="<?= htmlspecialchars('../assets/uploads/certificates/' . $filename) ?>" target="_blank">[Sijil]</a>
                            <?php endif; ?>
                        <?php else: ?>
                        Tiada
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        if ($row['approval_status'] === 'pending') {
                            echo 'Dalam Semakan';
                        } elseif ($row['approval_status'] === 'approved') {
                            echo 'Diluluskan';
                        } elseif ($row['approval_status'] === 'rejected') {
                            echo 'Ditolak';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (($row['approval_status'] === 'approved' || $row['approval_status'] === 'rejected') && !empty($row['approver_name'])) {
                            echo htmlspecialchars($row['approver_name']);
                        } elseif ($row['approval_status'] === 'approved' || $row['approval_status'] === 'rejected') {
                            echo '-';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td><?= $row['approved_at'] ? date('Y-m-d H:i', strtotime($row['approved_at'])) : '-' ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center;">Tiada rekod aktiviti ditemui.</td>
                </tr>
                <?php endif; ?>
            </tbody>
            </table>

    <?php else: ?>
        <p>Tiada permohonan dihantar.</p>
    <?php endif ?>
    </div>
</body>
</html>
