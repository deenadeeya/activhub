<?php
require_once '../includes/session_check.php';
include '../config/connect.php';
include '../includes/header.php';

// Check if user is logged in as teacher or admin
if (!isset($_SESSION['user_ic']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    header("Location: ../auth/login.php?expired=true");
    exit();
}

$user_ic = $_SESSION['user_ic'];
$user_role = $_SESSION['user_role'];

// Get events with detailed information
$events_query = "
    SELECT e.*, 
           cg.group_name,
           t.teacher_fname as creator_name,
           (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.event_id) as total_registrations,
           (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.event_id AND er.attendance_status = 'present') as total_attended
    FROM events e
    LEFT JOIN cocurricular_groups cg ON e.group_id = cg.group_id
    LEFT JOIN teacher t ON e.created_by = t.teacher_ic
    WHERE e.status = 'active'
    ORDER BY e.event_start_date ASC
";

$events_result = mysqli_query($conn, $events_query);

// Get pending approval count for teacher notification
$pending_count = 0;
if ($user_role === 'teacher') {
    $teacher_class_id = null;
    $sql_class_id = "SELECT class_id FROM class WHERE head_teacher = '$user_ic'";
    $result_class_id = mysqli_query($conn, $sql_class_id);
    if ($result_class_id && mysqli_num_rows($result_class_id) > 0) {
        $row_class_id = mysqli_fetch_assoc($result_class_id);
        $teacher_class_id = $row_class_id['class_id'];
        
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
    }
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />    <title>Pengurusan Acara - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/css/dash.css" />
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>        .container {
            background-color: rgba(224, 239, 220, 0.8);
            position: relative;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px 0;
        }

        .welcome-section {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
        }

        .welcome-section img {
            width: 80px;
            height: 80px;
            margin-right: 20px;
        }

        .welcome-texts h1 {
            font-size: 28px;
            margin: 0;
            text-align: center;
            color: black;
        }

        .welcome-texts h2 {
            font-size: 16px;
            margin: 5px 0 0 0;
            color: black;
            font-weight: normal;
        }

        .dashboard-content {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            position: relative;
            z-index: 10;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .left-panel {
            width: 300px;
            padding: 20px;
            background-color: white;
            opacity: 1;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .left-panel p {
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .salam {
            font-size: 25px;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
        }

        .left-panel button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }        .right-panel {
            flex: 1;
            max-width: 1000px;
            background-color: white;
            opacity: 1;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: left;
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

        .filter-section select, 
        .filter-section input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .event-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
            background: white;
        }

        .event-item strong {
            color: #064789;
            font-size: 16px;
        }

        .event-badges {
            display: flex;
            gap: 8px;
            margin: 10px 0;
            flex-wrap: wrap;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-meeting { background: #e3f2fd; color: #1976d2; }
        .badge-competition { background: #fff3e0; color: #f57c00; }
        .badge-training { background: #e8f5e8; color: #2e7d32; }
        .badge-social { background: #fce4ec; color: #ad1457; }
        .badge-other { background: #f5f5f5; color: #666; }
        .badge-mandatory { background: #ffebee; color: #c62828; }
        .badge-auto { background: #e8f5e8; color: #2e7d32; }
        .badge-public { background: #e3f2fd; color: #1976d2; }
        .badge-club_only { background: #fff3e0; color: #f57c00; }
        .badge-private { background: #fce4ec; color: #ad1457; }

        .event-stats {
            display: flex;
            gap: 15px;
            margin: 10px 0;
            flex-wrap: wrap;
        }

        .stat-item {
            background: #f5f5f5;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            min-width: 70px;
            font-size: 12px;
        }

        .stat-number {
            font-size: 16px;
            font-weight: bold;
            color: #064789;
            display: block;
        }

        .stat-label {
            font-size: 11px;
            color: #666;
        }

        .event-actions {
            margin-top: 10px;
        }

        .event-actions button {
            margin-top: 10px;
            margin-right: 5px;
            padding: 6px 10px;
            font-size: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .dashboard-content {
                flex-direction: column;
                align-items: center;
            }

            .left-panel,
            .right-panel {
                width: 90%;
            }

            .welcome-section {
                flex-direction: column;
            }

            .welcome-section img {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .filter-section {
                flex-direction: column;
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
            <?php if ($user_role === 'teacher'): ?>
                <button onclick="location.href='../forms/approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
                    <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">notifications</span>
                    <?php if ($pending_count > 0): ?>
                        <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 4px 7px; font-size: 12px;">
                            <?php echo $pending_count; ?>
                        </span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <div class="welcome-texts">
                <h1>PENGURUSAN ACARA KOKURIKULUM</h1>
                <h2>"Sistem Pengurusan dan Penjejakan Acara Kokurikulum"</h2>
            </div>        </div>

        <div class="right-panel" style="margin: 0 auto; max-width: 1200px;">
            <h3>SENARAI ACARA KOKURIKULUM</h3>
                
                <!-- Filters -->
                <div class="filter-section">
                    <label><strong>Tapis:</strong></label>
                    <select id="filter_type">
                        <option value="">Semua Jenis</option>
                        <option value="meeting">Mesyuarat</option>
                        <option value="competition">Pertandingan</option>
                        <option value="training">Latihan</option>
                        <option value="social">Sosial</option>
                        <option value="other">Lain-lain</option>
                    </select>
                    
                    <select id="filter_visibility">
                        <option value="">Semua Tahap Keterlihatan</option>
                        <option value="public">Awam</option>
                        <option value="club_only">Ahli Kelab Sahaja</option>
                        <option value="private">Peribadi</option>
                    </select>
                    
                    <input type="text" id="search_event" placeholder="Cari nama acara...">
                </div>

                <!-- Events List -->
                <div id="events-list">
                    <?php if ($events_result && mysqli_num_rows($events_result) > 0): ?>
                        <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                            <div class="event-item" 
                                 data-type="<?= htmlspecialchars($event['event_type']) ?>" 
                                 data-visibility="<?= htmlspecialchars($event['visibility']) ?>"
                                 data-name="<?= strtolower(htmlspecialchars($event['event_name'])) ?>">
                                
                                <strong><?= htmlspecialchars($event['event_name']) ?></strong><br>
                                
                                <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">calendar_today</span>
                                <?= date('d/m/Y', strtotime($event['event_start_date'])) ?>
                                <?php if ($event['event_start_date'] !== $event['event_end_date']): ?>
                                    - <?= date('d/m/Y', strtotime($event['event_end_date'])) ?>
                                <?php endif; ?><br>
                                
                                <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">location_on</span>
                                Tempat: <?= htmlspecialchars($event['event_venue']) ?><br>
                                
                                <?php if ($event['group_name']): ?>
                                    <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">group</span>
                                    Penganjur: <?= htmlspecialchars($event['group_name']) ?><br>
                                <?php endif; ?>

                                <?php if ($event['event_description']): ?>
                                    Keterangan: <?= nl2br(htmlspecialchars($event['event_description'])) ?><br>
                                <?php endif; ?>

                                <div class="event-badges">
                                    <span class="badge badge-<?= $event['event_type'] ?>"><?= ucfirst($event['event_type']) ?></span>
                                    
                                    <?php if ($event['is_mandatory']): ?>
                                        <span class="badge badge-mandatory">Wajib</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['auto_register_members']): ?>
                                        <span class="badge badge-auto">Auto-Daftar</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['visibility'] === 'public'): ?>
                                        <span class="badge badge-public">Awam</span>
                                    <?php elseif ($event['visibility'] === 'club_only'): ?>
                                        <span class="badge badge-club_only">Ahli Kelab</span>
                                    <?php else: ?>
                                        <span class="badge badge-private">Peribadi</span>
                                    <?php endif; ?>
                                </div>

                                <div class="event-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?= $event['total_registrations'] ?></span>
                                        <span class="stat-label">Pendaftar</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?= $event['total_attended'] ?></span>
                                        <span class="stat-label">Hadir</span>
                                    </div>
                                    <?php if ($event['max_participants']): ?>
                                        <div class="stat-item">
                                            <span class="stat-number"><?= $event['max_participants'] ?></span>
                                            <span class="stat-label">Had Peserta</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="event-actions">
                                    <button class="btn-status-blue" onclick="location.href='event_participants.php?event_id=<?= $event['event_id'] ?>'">Senarai Peserta</button>
                                    <button class="btn-status-blue" onclick="location.href='manage_attendance.php?id=<?= $event['event_id'] ?>'">Urus Kehadiran</button>
                                    <?php if ($user_role === 'admin' || $event['created_by'] === $user_ic): ?>
                                        <button class="btn-yellow" onclick="location.href='edit_events.php?id=<?= $event['event_id'] ?>'">Kemaskini</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="event-item" style="text-align: center; color: #666;">
                            <strong>Tiada Acara Dijumpai</strong><br>
                            Belum ada acara yang dicipta. <a href="add_events.php" style="color: #064789;">Klik di sini untuk menambah acara baru</a>.
                        </div>
                    <?php endif; ?>                </div>
            </div>
    </div><script>
        // Filter functionality
        function applyFilters() {
            const typeFilter = document.getElementById('filter_type').value;
            const visibilityFilter = document.getElementById('filter_visibility').value;
            const searchTerm = document.getElementById('search_event').value.toLowerCase();
            
            const eventItems = document.querySelectorAll('.event-item[data-type]');
            
            eventItems.forEach(item => {
                const itemType = item.getAttribute('data-type');
                const itemVisibility = item.getAttribute('data-visibility');
                const itemName = item.getAttribute('data-name');
                
                let show = true;
                
                if (typeFilter && itemType !== typeFilter) show = false;
                if (visibilityFilter && itemVisibility !== visibilityFilter) show = false;
                if (searchTerm && !itemName.includes(searchTerm)) show = false;
                
                item.style.display = show ? 'block' : 'none';
            });
        }

        // Add event listeners
        document.getElementById('filter_type').addEventListener('change', applyFilters);
        document.getElementById('filter_visibility').addEventListener('change', applyFilters);
        document.getElementById('search_event').addEventListener('input', applyFilters);
    </script>
</body>

</html>
