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

// Reset result pointer and count registration types
if ($students_result) {
    mysqli_data_seek($students_result, 0);
    while ($student = mysqli_fetch_assoc($students_result)) {
        if ($student['registration_type'] === 'auto') {
            $auto_registered_count++;
        } else {
            $manual_registered_count++;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        @media (max-width: 768px) {
            .event-stats {
                grid-template-columns: 1fr 1fr;
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

        <!-- Statistics -->
        <div class="event-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_registered; ?></div>
                <div class="stat-label">Jumlah Peserta</div>
            </div>
            <?php if ($auto_registered_count > 0): ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $auto_registered_count; ?></div>
                <div class="stat-label">Auto-Daftar</div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($students_result && mysqli_num_rows($students_result) > 0): ?>
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