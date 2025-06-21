<?php
require_once '../includes/session_check.php';
include '../config/connect.php';
include '../includes/header.php';

// Check if user is logged in as teacher or admin
if (!isset($_SESSION['user_ic']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    header("Location: ../auth/login.php?expired=true");
    exit();
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$event_id) {
    header("Location: manage_events.php");
    exit();
}

$user_ic = $_SESSION['user_ic'];
$user_role = $_SESSION['user_role'];

// Get event details
$event_query = "
    SELECT e.*, cg.group_name, t.teacher_fname as creator_name
    FROM events e
    LEFT JOIN cocurricular_groups cg ON e.group_id = cg.group_id
    LEFT JOIN teacher t ON e.created_by = t.teacher_ic
    WHERE e.event_id = ?
";
$stmt = $conn->prepare($event_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_result = $stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    header("Location: manage_events.php");
    exit();
}

// Handle attendance updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
    $attendance_data = $_POST['attendance'] ?? [];
    $notes_data = $_POST['notes'] ?? [];
    
    foreach ($attendance_data as $student_ic => $status) {
        $notes = $notes_data[$student_ic] ?? '';
        
        // Update attendance
        $update_query = "
            UPDATE event_registrations 
            SET attendance_status = ?, 
                attendance_marked_by = ?, 
                attendance_marked_at = CURRENT_TIMESTAMP,
                notes = ?
            WHERE event_id = ? AND student_ic = ?
        ";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssss", $status, $user_ic, $notes, $event_id, $student_ic);
        $update_stmt->execute();
    }
    
    $success_message = "Kehadiran berjaya dikemas kini!";
}

// Get registered students
$registrations_query = "
    SELECT er.*, s.student_fname, s.matrix, c.class_name,
           er.attendance_status, er.notes, er.registration_type,
           t.teacher_fname as marked_by_name
    FROM event_registrations er
    JOIN student s ON er.student_ic = s.student_ic
    JOIN class c ON s.student_class = c.class_id
    LEFT JOIN teacher t ON er.attendance_marked_by = t.teacher_ic
    WHERE er.event_id = ?
    ORDER BY s.student_fname ASC
";
$stmt = $conn->prepare($registrations_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$registrations_result = $stmt->get_result();

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_registered,
        SUM(CASE WHEN attendance_status = 'present' THEN 1 ELSE 0 END) as total_present,
        SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as total_absent,
        SUM(CASE WHEN attendance_status = 'late' THEN 1 ELSE 0 END) as total_late,
        SUM(CASE WHEN attendance_status = 'registered' THEN 1 ELSE 0 END) as total_pending
    FROM event_registrations 
    WHERE event_id = ?
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pengurusan Kehadiran - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link rel="stylesheet" href="../assets/css/history_table.css" />
    <link rel="stylesheet" href="../assets/css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
        .attendance-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .event-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-present { color: #28a745; }
        .stat-absent { color: #dc3545; }
        .stat-late { color: #ffc107; }
        .stat-pending { color: #6c757d; }
        .stat-total { color: #007bff; }

        .attendance-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .attendance-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .attendance-table th {
            background-color: #064789;
            color: white;
            font-weight: 500;
        }

        .attendance-table tr:hover {
            background-color: #f8f9fa;
        }

        .attendance-select {
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 100%;
        }

        .notes-input {
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 100%;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-auto { background: #d4edda; color: #155724; }
        .badge-manual { background: #cce5ff; color: #004085; }

        .bulk-actions {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .bulk-actions h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .attendance-table {
                overflow-x: auto;
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
                if ($user_role === 'admin') {
                    echo '<span class="admin-text">' . strtoupper($_SESSION['admin_name'] ?? 'ADMIN') . '</span><br>';
                } elseif ($user_role === 'teacher') {
                    $teacher_sql = "SELECT teacher_fname FROM teacher WHERE teacher_ic = '$user_ic'";
                    $teacher_result = mysqli_query($conn, $teacher_sql);
                    $teacher_data = mysqli_fetch_assoc($teacher_result);
                    echo '<span class="admin-text">' . strtoupper($teacher_data['teacher_fname']) . '</span><br>';
                }
                ?>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
        </div>
    </header>

    <div class="attendance-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>PENGURUSAN KEHADIRAN</h1>
            <a href="manage_events.php" class="btn-red">Kembali</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <!-- Event Information -->
        <div class="event-info">
            <h2><?= htmlspecialchars($event['event_name']) ?></h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <div><strong>Tarikh:</strong> <?= date('d/m/Y', strtotime($event['event_start_date'])) ?></div>
                <div><strong>Masa:</strong> <?= date('H:i', strtotime($event['event_start_date'])) ?></div>
                <div><strong>Tempat:</strong> <?= htmlspecialchars($event['event_venue']) ?></div>
                <?php if ($event['group_name']): ?>
                    <div><strong>Penganjur:</strong> <?= htmlspecialchars($event['group_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number stat-total"><?= $stats['total_registered'] ?></div>
                <div>Jumlah Pendaftar</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-present"><?= $stats['total_present'] ?></div>
                <div>Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-absent"><?= $stats['total_absent'] ?></div>
                <div>Tidak Hadir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-late"><?= $stats['total_late'] ?></div>
                <div>Lewat</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-pending"><?= $stats['total_pending'] ?></div>
                <div>Belum Ditanda</div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <h4>Tindakan Pukal</h4>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" onclick="markAllAs('present')" class="btn-green">Tandakan Semua Hadir</button>
                <button type="button" onclick="markAllAs('absent')" class="btn-red">Tandakan Semua Tidak Hadir</button>
                <button type="button" onclick="markAllAs('registered')" class="btn-yellow">Reset Semua</button>
            </div>
        </div>

        <!-- Attendance Table -->
        <form method="POST">
            <div class="attendance-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 25%">Nama Pelajar</th>
                            <th style="width: 15%">No. Matrik</th>
                            <th style="width: 10%">Kelas</th>
                            <th style="width: 10%">Jenis Daftar</th>
                            <th style="width: 15%">Status Kehadiran</th>
                            <th style="width: 20%">Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        if ($registrations_result && mysqli_num_rows($registrations_result) > 0): 
                        ?>
                            <?php while ($registration = mysqli_fetch_assoc($registrations_result)): ?>
                                <tr>
                                    <td><?= $counter++ ?></td>
                                    <td><strong><?= htmlspecialchars($registration['student_fname']) ?></strong></td>
                                    <td><?= htmlspecialchars($registration['matrix']) ?></td>
                                    <td><?= htmlspecialchars($registration['class_name']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $registration['registration_type'] ?>">
                                            <?= $registration['registration_type'] === 'auto' ? 'Auto' : 'Manual' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select name="attendance[<?= $registration['student_ic'] ?>]" class="attendance-select">
                                            <option value="registered" <?= $registration['attendance_status'] === 'registered' ? 'selected' : '' ?>>Belum Ditanda</option>
                                            <option value="present" <?= $registration['attendance_status'] === 'present' ? 'selected' : '' ?>>Hadir</option>
                                            <option value="absent" <?= $registration['attendance_status'] === 'absent' ? 'selected' : '' ?>>Tidak Hadir</option>
                                            <option value="late" <?= $registration['attendance_status'] === 'late' ? 'selected' : '' ?>>Lewat</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="notes[<?= $registration['student_ic'] ?>]" 
                                               value="<?= htmlspecialchars($registration['notes'] ?? '') ?>"
                                               placeholder="Nota tambahan..."
                                               class="notes-input">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #666;">
                                    Tiada pendaftaran untuk acara ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($registrations_result && mysqli_num_rows($registrations_result) > 0): ?>
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" name="update_attendance" class="btn-darkblue" style="padding: 12px 30px;">
                        Simpan Kehadiran
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        function markAllAs(status) {
            const selects = document.querySelectorAll('.attendance-select');
            selects.forEach(select => {
                select.value = status;
            });
        }

        // Auto-save functionality (optional)
        let saveTimeout;
        function autoSave() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                // Could implement AJAX auto-save here
                console.log('Auto-save triggered');
            }, 2000);
        }

        // Add change listeners for auto-save
        document.querySelectorAll('.attendance-select, .notes-input').forEach(element => {
            element.addEventListener('change', autoSave);
        });
    </script>
</body>

</html>
