<?php
require_once '../config/connect.php';
require_once '../includes/session_check.php';
include '../includes/header.php';

// Validate and fetch the event_id
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo "Invalid event ID.";
    exit();
}

$event_id = intval($_GET['event_id']);

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $student_ic = $_POST['student_ic'];
    $attendance_status = $_POST['attendance_status'];
    
    // Update attendance status
    $update_sql = "UPDATE event_registrations 
                   SET attendance_status = ? 
                   WHERE event_id = ? AND student_ic = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sis", $attendance_status, $event_id, $student_ic);
    
    if ($stmt->execute()) {
        $success_message = "Kehadiran telah dikemaskini!";
    } else {
        $error_message = "Ralat semasa kemaskini kehadiran!";
    }
    $stmt->close();
}

// Handle bulk attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_attendance'])) {
    $bulk_status = $_POST['bulk_status'];
    
    $update_sql = "UPDATE event_registrations 
                   SET attendance_status = ? 
                   WHERE event_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $bulk_status, $event_id);
    
    if ($stmt->execute()) {
        $success_message = "Semua kehadiran telah dikemaskini!";
    } else {
        $error_message = "Ralat semasa kemaskini kehadiran!";
    }
    $stmt->close();
}

// Fetch event name
$event_sql = "SELECT event_name FROM events WHERE event_id = $event_id";
$event_result = mysqli_query($conn, $event_sql);

if (!$event_result || mysqli_num_rows($event_result) === 0) {
    echo "Event not found.";
    exit();
}

// Fetch event details
$event_sql = "
    SELECT e.*, cg.group_name 
    FROM events e 
    LEFT JOIN cocurricular_groups cg ON e.group_id = cg.group_id 
    WHERE e.event_id = $event_id
";
$event_result = mysqli_query($conn, $event_sql);

if (!$event_result || mysqli_num_rows($event_result) === 0) {
    echo "Event not found.";
    exit();
}

$event = mysqli_fetch_assoc($event_result);

// Fetch registered students with class information
$students_sql = "
    SELECT s.student_fname, s.student_ic, c.class_year, c.class_name, 
           er.registration_type, er.registration_date, er.attendance_status
    FROM event_registrations er
    JOIN student s ON er.student_ic = s.student_ic
    LEFT JOIN class c ON s.student_class = c.class_id
    WHERE er.event_id = $event_id
    ORDER BY c.class_year ASC, c.class_name ASC, s.student_fname ASC
";

$students_result = mysqli_query($conn, $students_sql);

// Count statistics
$total_registered = mysqli_num_rows($students_result);
$auto_registered_count = 0;
$manual_registered_count = 0;
$present_count = 0;
$absent_count = 0;
$pending_count = 0;

// Reset result pointer and count registration types and attendance
if ($students_result) {
    mysqli_data_seek($students_result, 0);
    while ($student = mysqli_fetch_assoc($students_result)) {
        if ($student['registration_type'] === 'auto') {
            $auto_registered_count++;
        } else {
            $manual_registered_count++;
        }
        
        // Count attendance
        switch ($student['attendance_status']) {
            case 'present':
                $present_count++;
                break;
            case 'absent':
                $absent_count++;
                break;
            default:
                $pending_count++;
                break;
        }
    }
    // Reset result pointer for display
    mysqli_data_seek($students_result, 0);
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peserta Acara - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/dash.css">
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link rel="stylesheet" href="../assets/css/cocurricular.css" />
    <link rel="stylesheet" href="../assets/css/history_table.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
        .event-summary {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #064789;
        }
        .event-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #064789;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .additional-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .participant-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .registration-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .badge-auto {
            background: #e7f3ff;
            color: #0066cc;
        }
        .badge-manual {
            background: #f0f9ff;
            color: #0891b2;
        }
        .attendance-select {
            padding: 5px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            background: white;
            min-width: 80px;
        }
        .attendance-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        .badge-present {
            background: #dcfce7;
            color: #166534;
        }
        .badge-absent {
            background: #fecaca;
            color: #dc2626;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .mark-all-section {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 2px;
        }
        .btn-present { background: #22c55e; color: white; }
        .btn-absent { background: #ef4444; color: white; }
        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #22c55e;
        }
        .error-message {
            background: #fecaca;
            color: #dc2626;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }
        @media (max-width: 768px) {
            .event-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .additional-stats {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 480px) {
            .event-stats {
                grid-template-columns: 1fr;
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
      <span class="material-symbols-outlined icon">notifications</span>
    </div>
  </header>

    <div class="container">
        <h1>SENARAI PESERTA ACARA</h1>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="success-message">✅ <?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="error-message">❌ <?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Event Summary -->
        <div class="event-summary">
            <h2 style="margin: 0 0 15px 0; color: #064789;"><?php echo strtoupper(htmlspecialchars($event['event_name'])); ?></h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <strong>📅 Tarikh:</strong> 
                    <?php 
                    echo date('d/m/Y', strtotime($event['event_start_date'])); 
                    if ($event['event_start_date'] !== $event['event_end_date']) {
                        echo ' - ' . date('d/m/Y', strtotime($event['event_end_date']));
                    }
                    ?>
                </div>
                <div><strong>📍 Tempat:</strong> <?php echo htmlspecialchars($event['event_venue']); ?></div>
                <div><strong>🏢 Jenis:</strong> <?php echo ucfirst($event['event_type']); ?></div>
                <?php if ($event['group_name']): ?>
                <div><strong>👥 Penganjur:</strong> <?php echo htmlspecialchars($event['group_name']); ?></div>
                <?php endif; ?>
            </div>

            <?php if ($event['event_description']): ?>
            <div style="margin-top: 15px;">
                <strong>📝 Penerangan:</strong>
                <p style="margin: 5px 0; color: #555;"><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($event['is_mandatory']): ?>
            <div style="margin-top: 10px;">
                <span style="background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 20px; font-size: 0.9em; font-weight: bold;">
                    ⚠️ ACARA WAJIB
                </span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Attendance Statistics -->
        <div class="event-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_registered; ?></div>
                <div class="stat-label">Jumlah Peserta</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #22c55e;"><?php echo $present_count; ?></div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ef4444;"><?php echo $absent_count; ?></div>
                <div class="stat-label">Tidak Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #f59e0b;"><?php echo $pending_count; ?></div>
                <div class="stat-label">Belum Ditanda</div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <?php if ($auto_registered_count > 0): ?>
        <div class="additional-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $auto_registered_count; ?></div>
                <div class="stat-label">Auto-Daftar</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($students_result && mysqli_num_rows($students_result) > 0): ?>
            
            <!-- Bulk Attendance Marking (Admin/Teacher Only) -->
            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')): ?>
            <div class="mark-all-section">
                <h3 style="margin: 0 0 15px 0; color: #064789;">Tanda Kehadiran Semua</h3>
                <form method="POST" style="display: inline-block;" onsubmit="return confirm('Adakah anda pasti mahu menandakan semua peserta sebagai ' + this.bulk_status.value + '?');">
                    <input type="hidden" name="mark_all_attendance" value="1">
                    <button type="submit" name="bulk_status" value="present" class="btn-small btn-present">✅ Tandakan Semua Hadir</button>
                    <button type="submit" name="bulk_status" value="absent" class="btn-small btn-absent">❌ Tandakan Semua Tidak Hadir</button>
                    <button type="submit" name="bulk_status" value="" class="btn-small" style="background: #f59e0b; color: white;">🔄 Reset Semua</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="participant-table">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Pelajar</th>
                            <th>Tahun</th>
                            <th>Kelas</th>
                            <th>No. K/P</th>
                            <th>Tarikh Daftar</th>
                            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')): ?>
                            <th>Kehadiran</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        while ($student = mysqli_fetch_assoc($students_result)):
                        ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['student_fname']); ?></strong>
                                </td>
                                <td><?php echo $student['class_year'] ?? 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['student_ic']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($student['registration_date'])); ?></td>
                                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')): ?>
                                <td>
                                    <form method="POST" style="display: inline-block; margin: 0;">
                                        <input type="hidden" name="mark_attendance" value="1">
                                        <input type="hidden" name="student_ic" value="<?php echo htmlspecialchars($student['student_ic']); ?>">
                                        <select name="attendance_status" class="attendance-select" onchange="this.form.submit();">
                                            <option value="" <?php echo ($student['attendance_status'] === '' || $student['attendance_status'] === null) ? 'selected' : ''; ?>>Belum Ditanda</option>
                                            <option value="present" <?php echo $student['attendance_status'] === 'present' ? 'selected' : ''; ?>>✅ Hadir</option>
                                            <option value="absent" <?php echo $student['attendance_status'] === 'absent' ? 'selected' : ''; ?>>❌ Tidak Hadir</option>
                                        </select>
                                    </form>
                                    
                                    <!-- Display current status as badge -->
                                    <div style="margin-top: 5px;">
                                        <?php if ($student['attendance_status'] === 'present'): ?>
                                            <span class="attendance-badge badge-present">✅ Hadir</span>
                                        <?php elseif ($student['attendance_status'] === 'absent'): ?>
                                            <span class="attendance-badge badge-absent">❌ Tidak Hadir</span>
                                        <?php else: ?>
                                            <span class="attendance-badge badge-pending">⏳ Belum Ditanda</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="font-size: 4em; color: #ddd; margin-bottom: 15px;">👥</div>
                <h3 style="color: #666; margin-bottom: 10px;">Tiada Peserta</h3>
                <p style="color: #999;">Belum ada pelajar yang mendaftar untuk acara ini.</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center;">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="../admin/admin_dashboard.php" class="btn-darkblue">Kembali ke Dashboard</a>
                <a href="manage_events.php" class="btn-red" style="margin-left: 10px;">Urus Acara</a>
            <?php else: ?>
                <a href="../teacher/teacher_dashboard.php" class="btn-darkblue">Kembali ke Dashboard</a>
            <?php endif; ?>
            
        </div>
    </div>
</body>

</html>