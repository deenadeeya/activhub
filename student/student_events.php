<?php
require_once '../config/connect.php';
require_once '../includes/session_check.php';
require_once '../includes/NotificationService.php';
include '../includes/header.php';

if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../auth/login.php?expired=true");
    exit();
}

$user_ic = $_SESSION['user_ic'];

// Get student info
$sql = "
    SELECT s.*, c.class_year 
    FROM student s
    JOIN class c ON s.student_class = c.class_id
    WHERE s.student_ic = '$user_ic'
";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) === 1) {
    $student = mysqli_fetch_assoc($result);
} else {
    header("Location: ../auth/login.php?expired=true");
    exit();
}

// Handle cancellation
$cancellation_success = false;
$cancellation_error = false;
if (isset($_GET['cancel']) && isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);
    
    // Check if event hasn't started yet
    $check_event = "SELECT event_start_date FROM events WHERE event_id = $event_id";
    $check_result = mysqli_query($conn, $check_event);
    
    if ($check_result && $event_data = mysqli_fetch_assoc($check_result)) {
        $event_start = strtotime($event_data['event_start_date']);
        $now = time();
        
        if ($now < $event_start) {
            // Delete registration
            $cancel_query = "DELETE FROM event_registrations WHERE student_ic = '$user_ic' AND event_id = $event_id";
            if (mysqli_query($conn, $cancel_query)) {
                // Also remove from cocu_activities if it was auto-added
                $remove_activity = "DELETE FROM cocu_activities WHERE student_ic = '$user_ic' AND activity_name = (SELECT event_name FROM events WHERE event_id = $event_id)";
                mysqli_query($conn, $remove_activity);
                $cancellation_success = true;
            } else {
                $cancellation_error = true;
            }
        } else {
            $cancellation_error = true;
        }
    }
}

// Get all registered events for this student
$events_query = "
    SELECT e.*, g.group_name, er.registration_date, er.attendance_status,
           (SELECT COUNT(*) FROM event_registrations er2 WHERE er2.event_id = e.event_id) as total_participants
    FROM event_registrations er
    JOIN events e ON er.event_id = e.event_id
    LEFT JOIN cocurricular_groups g ON e.group_id = g.group_id
    WHERE er.student_ic = '$user_ic'
    ORDER BY e.event_start_date DESC
";

$events_result = mysqli_query($conn, $events_query);

// Get summary statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_registered,
        SUM(CASE WHEN er.attendance_status = 'present' THEN 1 ELSE 0 END) as total_attended,
        SUM(CASE WHEN e.event_start_date > NOW() THEN 1 ELSE 0 END) as upcoming_events
    FROM event_registrations er
    JOIN events e ON er.event_id = e.event_id
    WHERE er.student_ic = '$user_ic'
";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acara Saya - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/css/dash.css" />
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
        .stats-cards {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: #064789;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            min-width: 150px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 2rem;
            margin: 0 0 5px 0;
            font-weight: bold;
        }

        .stat-card p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .events-section {
            margin-bottom: 30px;
        }

        .events-section h2 {
            color: #064789;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }

        .my-event-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #064789;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .my-event-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .my-event-item.upcoming {
            border-left-color: #28a745;
        }

        .my-event-item.ongoing {
            border-left-color: #ffc107;
        }

        .my-event-item.completed {
            border-left-color: #6c757d;
        }

        .event-status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .event-status-badge.upcoming {
            background: #d4edda;
            color: #155724;
        }

        .event-status-badge.ongoing {
            background: #fff3cd;
            color: #856404;
        }

        .event-status-badge.completed {
            background: #e2e3e5;
            color: #383d41;
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #064789;
            margin-bottom: 10px;
        }

        .event-description {
            color: #666;
            font-style: italic;
            margin-bottom: 12px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .event-details {
            color: #555;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .event-details strong {
            color: #333;
        }

        .event-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .attendance-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-top: 8px;
        }

        .attendance-present {
            background: #d4edda;
            color: #155724;
        }

        .attendance-absent {
            background: #f8d7da;
            color: #721c24;
        }

        .attendance-pending {
            background: #fff3cd;
            color: #856404;
        }

        .no-events-message {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            margin: 40px 0;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .stats-cards {
                gap: 10px;
            }

            .stat-card {
                min-width: 120px;
                padding: 15px;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .my-event-item {
                padding: 15px;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    </style>
    <script>
        function confirmCancellation(eventId, eventName) {
            if (confirm(`Adakah anda pasti mahu batalkan pendaftaran untuk "${eventName}"?`)) {
                window.location.href = `student_events.php?cancel=true&event_id=${eventId}`;
            }
        }
    </script>
</head>

<body>
    <?php if ($cancellation_success): ?>
        <script>
            alert("Pendaftaran berjaya dibatalkan!");
        </script>
    <?php endif; ?>

    <?php if ($cancellation_error): ?>
        <script>
            alert("Ralat: Tidak dapat membatalkan pendaftaran. Acara mungkin telah bermula.");
        </script>
    <?php endif; ?>

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
                <span class="admin-text"><?php echo strtoupper($student['student_fname']); ?></span><br>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <?php include '../includes/notifications_panel.php'; ?>
        </div>
    </header>    <div class="container">
        <div class="welcome-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <div class="welcome-texts">
                <h1>📅 ACARA SAYA</h1>
                <h2>"Senarai acara yang telah anda daftarkan"</h2>
            </div>
        </div>

        <br>

        <div class="stats-cards">
            <div class="stat-card">
                <h3><?php echo $stats['total_registered'] ?? 0; ?></h3>
                <p>Jumlah Acara Didaftar</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_attended'] ?? 0; ?></h3>
                <p>Acara Dihadiri</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['upcoming_events'] ?? 0; ?></h3>
                <p>Acara Akan Datang</p>
            </div>
        </div>        <div class="card">
            <div class="events-section">
                <?php if ($events_result && mysqli_num_rows($events_result) > 0): ?>
                    <div class="events-grid">
                        <?php while ($event = mysqli_fetch_assoc($events_result)): 
                            $start = strtotime($event['event_start_date']);
                            $end = strtotime($event['event_end_date']);
                            $now = time();
                            
                            if ($now < $start) {
                                $status = 'upcoming';
                                $status_text = 'Akan Datang';
                                $can_cancel = true;
                            } elseif ($now >= $start && $now <= $end) {
                                $status = 'ongoing';
                                $status_text = 'Sedang Berlangsung';
                                $can_cancel = false;
                            } else {
                                $status = 'completed';
                                $status_text = 'Telah Selesai';
                                $can_cancel = false;
                            }
                        ?>
                            <div class="my-event-item <?php echo $status; ?>">
                                <div class="event-status-badge <?php echo $status; ?>">
                                    <?php echo $status_text; ?>
                                </div>
                                  <div class="event-title">
                                    <?php echo htmlspecialchars($event['event_name']); ?>
                                </div>
                                
                                <?php if (!empty($event['event_description'])): ?>
                                    <div class="event-description">
                                        <?php echo htmlspecialchars($event['event_description']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="event-details">
                                    <strong>📅 Tarikh:</strong> <?php echo date('d M Y', strtotime($event['event_start_date'])); ?>
                                    <?php if ($event['event_start_date'] !== $event['event_end_date']): ?>
                                        - <?php echo date('d M Y', strtotime($event['event_end_date'])); ?>
                                    <?php endif; ?><br>
                                    
                                    <strong>📍 Tempat:</strong> <?php echo htmlspecialchars($event['event_venue']); ?><br>
                                    
                                    <strong>🏢 Penganjur:</strong> <?php echo $event['group_name'] ? htmlspecialchars($event['group_name']) : 'Acara Luar'; ?><br>
                                    
                                    <strong>📝 Didaftar:</strong> <?php echo date('d M Y, g:i A', strtotime($event['registration_date'])); ?><br>
                                    
                                    <strong>👥 Peserta:</strong> <?php echo $event['total_participants']; ?> orang
                                    
                                    <?php if ($event['attendance_status']): ?>
                                        <div class="attendance-badge attendance-<?php echo $event['attendance_status']; ?>">
                                            <?php 
                                            switch($event['attendance_status']) {
                                                case 'present': echo '✅ Hadir'; break;
                                                case 'absent': echo '❌ Tidak Hadir'; break;
                                                default: echo '⏳ Kehadiran Belum Ditandakan'; break;
                                            }
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="attendance-badge attendance-pending">
                                            ⏳ Kehadiran Belum Ditandakan
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($can_cancel): ?>
                                    <div class="event-actions">
                                        <button class="btn-red" onclick="confirmCancellation(<?php echo $event['event_id']; ?>, '<?php echo addslashes($event['event_name']); ?>')">
                                            Batal Pendaftaran
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-events-message">
                        <p><strong>📭 Anda belum mendaftar untuk sebarang acara.</strong></p>
                        <p>Sila kembali ke dashboard untuk melihat acara yang tersedia.</p>
                        <br>
                        <button class="btn-yellow" onclick="location.href='student_dashboard.php'">
                            Kembali ke Dashboard
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($events_result && mysqli_num_rows($events_result) > 0): ?>
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn-yellow" onclick="location.href='student_dashboard.php'">
                    ← Kembali ke Dashboard
                </button>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
