<?php
require_once '../includes/session_check.php';
include '../config/connect.php';
include '../includes/header.php';

// Check if user is logged in as student
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../auth/login.php?expired=true");
    exit();
}

$student_ic = $_SESSION['user_ic'];

// Get student's class and club memberships for visibility filtering
$student_query = "
    SELECT s.*, c.class_name, c.class_year,
           GROUP_CONCAT(scm.group_id) as club_ids
    FROM student s
    JOIN class c ON s.student_class = c.class_id
    LEFT JOIN student_club_membership scm ON s.student_ic = scm.student_ic
    WHERE s.student_ic = ?
    GROUP BY s.student_ic
";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("s", $student_ic);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();

$student_club_ids = $student['club_ids'] ? explode(',', $student['club_ids']) : [];
$student_year = $student['class_year'];

// Build events query with visibility controls
$events_query = "
    SELECT e.*, cg.group_name,
           er.registration_id, er.attendance_status, er.registration_type,
           en.notification_id, en.is_read as notification_read
    FROM events e
    LEFT JOIN cocurricular_groups cg ON e.group_id = cg.group_id
    LEFT JOIN event_registrations er ON e.event_id = er.event_id AND er.student_ic = ?
    LEFT JOIN event_notifications en ON e.event_id = en.event_id AND en.student_ic = ? AND en.is_read = 0
    WHERE e.status = 'active' AND e.event_start_date >= CURDATE()
    AND (
        e.visibility = 'public' 
        OR (e.visibility = 'club_only' AND e.group_id IN (" . str_repeat('?,', count($student_club_ids) - 1) . "?))
    )
";

// Add eligible years filter
$events_query .= " AND (e.eligible_years IS NULL OR e.eligible_years = '' OR FIND_IN_SET(?, e.eligible_years) > 0)";
$events_query .= " ORDER BY e.event_start_date ASC";

$stmt = $conn->prepare($events_query);
$params = [$student_ic, $student_ic];
$params = array_merge($params, $student_club_ids);
$params[] = $student_year;

$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$events_result = $stmt->get_result();

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    $event_id = intval($_POST['event_id']);
    
    // Check if already registered
    $check_query = "SELECT registration_id FROM event_registrations WHERE event_id = ? AND student_ic = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("is", $event_id, $student_ic);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        // Register for event
        $register_query = "INSERT INTO event_registrations (event_id, student_ic, registration_type) VALUES (?, ?, 'manual')";
        $register_stmt = $conn->prepare($register_query);
        $register_stmt->bind_param("is", $event_id, $student_ic);
        
        if ($register_stmt->execute()) {
            $success_message = "Berjaya mendaftar untuk acara!";
            // Refresh the page to update registration status
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Mark notifications as read
if (isset($_GET['read_notification'])) {
    $notification_id = intval($_GET['read_notification']);
    $read_query = "UPDATE event_notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE notification_id = ? AND student_ic = ?";
    $read_stmt = $conn->prepare($read_query);
    $read_stmt->bind_param("is", $notification_id, $student_ic);
    $read_stmt->execute();
}

// Get notification count
$notif_query = "SELECT COUNT(*) as unread_count FROM event_notifications WHERE student_ic = ? AND is_read = 0";
$notif_stmt = $conn->prepare($notif_query);
$notif_stmt->bind_param("s", $student_ic);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_data = $notif_result->fetch_assoc();
$unread_notifications = $notif_data['unread_count'];
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acara Kokurikulum - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link rel="stylesheet" href="../assets/css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
        .events-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .event-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            border-left: 4px solid #064789;
            position: relative;
        }

        .event-card.new-notification {
            border-left-color: #ff6b6b;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
            50% { box-shadow: 0 2px 20px rgba(255,107,107,0.3); }
            100% { box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #064789;
            margin: 0 0 10px 0;
        }

        .event-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }

        .event-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-mandatory { background: #ffebee; color: #c62828; }
        .badge-auto { background: #e8f5e8; color: #2e7d32; }
        .badge-club { background: #fff3e0; color: #f57c00; }
        .badge-registered { background: #e3f2fd; color: #1976d2; }
        .badge-present { background: #e8f5e8; color: #2e7d32; }

        .event-description {
            color: #555;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .registration-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .notification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff6b6b;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-section select, .filter-section input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .event-meta {
                flex-direction: column;
                gap: 8px;
            }
            
            .registration-status {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
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
                <span class="admin-text"><?= strtoupper($student['student_fname']) ?></span><br>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <button onclick="toggleNotifications()" style="position: relative; background: none; border: none; cursor: pointer;">
                <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">notifications</span>
                <?php if ($unread_notifications > 0): ?>
                    <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 4px 7px; font-size: 12px;">
                        <?= $unread_notifications ?>
                    </span>
                <?php endif; ?>
            </button>
        </div>
    </header>

    <div class="events-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>ACARA KOKURIKULUM</h1>
            <a href="../student/student_dashboard.php" class="btn-red">Kembali</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <label><strong>Tapis:</strong></label>
            <select id="filter_status">
                <option value="">Semua Acara</option>
                <option value="registered">Sudah Daftar</option>
                <option value="not_registered">Belum Daftar</option>
                <option value="my_clubs">Acara Kelab Saya</option>
            </select>
            <input type="text" id="search_event" placeholder="Cari nama acara...">
        </div>

        <!-- Events List -->
        <div id="events-list">
            <?php if ($events_result && mysqli_num_rows($events_result) > 0): ?>
                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                    <div class="event-card <?= $event['notification_id'] ? 'new-notification' : '' ?>" 
                         data-status="<?= $event['registration_id'] ? 'registered' : 'not_registered' ?>"
                         data-club="<?= $event['group_id'] && in_array($event['group_id'], $student_club_ids) ? 'my_clubs' : 'other' ?>"
                         data-name="<?= strtolower(htmlspecialchars($event['event_name'])) ?>">
                        
                        <?php if ($event['notification_id']): ?>
                            <div class="notification-badge">BARU!</div>
                        <?php endif; ?>

                        <h3 class="event-title"><?= htmlspecialchars($event['event_name']) ?></h3>
                        
                        <div class="event-meta">
                            <div class="meta-item">
                                <span class="material-symbols-outlined" style="font-size: 16px;">calendar_today</span>
                                <?= date('d/m/Y', strtotime($event['event_start_date'])) ?>
                                <?php if ($event['event_start_date'] !== $event['event_end_date']): ?>
                                    - <?= date('d/m/Y', strtotime($event['event_end_date'])) ?>
                                <?php endif; ?>
                            </div>
                            <div class="meta-item">
                                <span class="material-symbols-outlined" style="font-size: 16px;">location_on</span>
                                <?= htmlspecialchars($event['event_venue']) ?>
                            </div>
                            <?php if ($event['group_name']): ?>
                                <div class="meta-item">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">group</span>
                                    <?= htmlspecialchars($event['group_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="event-badges">
                            <span class="badge"><?= ucfirst($event['event_type']) ?></span>
                            
                            <?php if ($event['is_mandatory']): ?>
                                <span class="badge badge-mandatory">Wajib</span>
                            <?php endif; ?>
                            
                            <?php if ($event['registration_type'] === 'auto'): ?>
                                <span class="badge badge-auto">Auto-Daftar</span>
                            <?php endif; ?>
                            
                            <?php if ($event['visibility'] === 'club_only'): ?>
                                <span class="badge badge-club">Ahli Kelab</span>
                            <?php endif; ?>

                            <?php if ($event['registration_id']): ?>
                                <?php if ($event['attendance_status'] === 'present'): ?>
                                    <span class="badge badge-present">Hadir</span>
                                <?php else: ?>
                                    <span class="badge badge-registered">Didaftarkan</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($event['event_description']): ?>
                            <div class="event-description">
                                <?= nl2br(htmlspecialchars($event['event_description'])) ?>
                            </div>
                        <?php endif; ?>

                        <div class="registration-status">
                            <?php if ($event['registration_id']): ?>
                                <div>
                                    <strong>Status:</strong> 
                                    <?php
                                    switch ($event['attendance_status']) {
                                        case 'present': echo '<span style="color: #28a745;">Hadir</span>'; break;
                                        case 'absent': echo '<span style="color: #dc3545;">Tidak Hadir</span>'; break;
                                        case 'late': echo '<span style="color: #ffc107;">Lewat</span>'; break;
                                        default: echo '<span style="color: #007bff;">Didaftarkan</span>';
                                    }
                                    ?>
                                    <?php if ($event['registration_type'] === 'auto'): ?>
                                        <small>(Auto-daftar oleh kelab)</small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($event['registration_deadline'] && strtotime($event['registration_deadline']) < time()): ?>
                                    <small style="color: #666;">Tempoh pendaftaran telah tamat</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (!$event['registration_deadline'] || strtotime($event['registration_deadline']) >= time()): ?>
                                    <div>Status: <span style="color: #666;">Belum Didaftarkan</span></div>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <button type="submit" name="register_event" class="btn-yellow">Daftar Sekarang</button>
                                    </form>
                                <?php else: ?>
                                    <div>Status: <span style="color: #dc3545;">Tempoh pendaftaran telah tamat</span></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="event-card" style="text-align: center; color: #666;">
                    <h3>Tiada Acara Dijumpai</h3>
                    <p>Tiada acara yang tersedia untuk anda pada masa ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Filter functionality
        function applyFilters() {
            const statusFilter = document.getElementById('filter_status').value;
            const searchTerm = document.getElementById('search_event').value.toLowerCase();
            
            const eventCards = document.querySelectorAll('.event-card[data-status]');
            
            eventCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const cardClub = card.getAttribute('data-club');
                const cardName = card.getAttribute('data-name');
                
                let show = true;
                
                if (statusFilter === 'registered' && cardStatus !== 'registered') show = false;
                if (statusFilter === 'not_registered' && cardStatus !== 'not_registered') show = false;
                if (statusFilter === 'my_clubs' && cardClub !== 'my_clubs') show = false;
                if (searchTerm && !cardName.includes(searchTerm)) show = false;
                
                card.style.display = show ? 'block' : 'none';
            });
        }

        // Add event listeners
        document.getElementById('filter_status').addEventListener('change', applyFilters);
        document.getElementById('search_event').addEventListener('input', applyFilters);

        // Notification toggle (placeholder)
        function toggleNotifications() {
            // Could implement a dropdown or modal for notifications
            alert('Sistem notifikasi akan dibangunkan lebih lanjut!');
        }
    </script>
</body>

</html>
