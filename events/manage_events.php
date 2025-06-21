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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pengurusan Acara - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link rel="stylesheet" href="../assets/css/history_table.css" />
    <link rel="stylesheet" href="../assets/css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
        .events-container {
            max-width: 1200px;
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
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #064789;
            margin: 0 0 5px 0;
        }

        .event-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
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
            margin-bottom: 10px;
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
        .badge-public { background: #e3f2fd; color: #1976d2; }
        .badge-private { background: #fce4ec; color: #ad1457; }

        .event-description {
            color: #555;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .event-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .stat-item {
            background: #f5f5f5;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            min-width: 80px;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #064789;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }

        .event-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
            .event-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .event-meta {
                flex-direction: column;
                gap: 8px;
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

    <div class="events-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>PENGURUSAN ACARA KOKURIKULUM</h1>
            <div>
                <a href="add_events.php" class="btn-yellow">Tambah Acara Baru</a>
                <?php if ($user_role === 'admin'): ?>
                    <a href="../admin/admin_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
                <?php else: ?>
                    <a href="../teacher/teacher_dashboard.php" class="btn-red" style="margin-left: 10px;">Kembali</a>
                <?php endif; ?>
            </div>
        </div>

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
                    <div class="event-card" 
                         data-type="<?= htmlspecialchars($event['event_type']) ?>" 
                         data-visibility="<?= htmlspecialchars($event['visibility']) ?>"
                         data-name="<?= strtolower(htmlspecialchars($event['event_name'])) ?>">
                        
                        <div class="event-header">
                            <div>
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
                            </div>
                        </div>

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
                                <span class="badge badge-club">Ahli Kelab</span>
                            <?php else: ?>
                                <span class="badge badge-private">Peribadi</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($event['event_description']): ?>
                            <div class="event-description">
                                <?= nl2br(htmlspecialchars($event['event_description'])) ?>
                            </div>
                        <?php endif; ?>

                        <div class="event-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?= $event['total_registrations'] ?></div>
                                <div class="stat-label">Pendaftar</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= $event['total_attended'] ?></div>
                                <div class="stat-label">Hadir</div>
                            </div>
                            <?php if ($event['max_participants']): ?>
                                <div class="stat-item">
                                    <div class="stat-number"><?= $event['max_participants'] ?></div>
                                    <div class="stat-label">Had Peserta</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="event-actions">
                            <a href="view_event.php?id=<?= $event['event_id'] ?>" class="btn-darkblue">Lihat Butiran</a>
                            <a href="manage_attendance.php?id=<?= $event['event_id'] ?>" class="btn-yellow">Kehadiran</a>
                            <?php if ($user_role === 'admin' || $event['created_by'] === $user_ic): ?>
                                <a href="edit_events.php?id=<?= $event['event_id'] ?>" class="btn-green">Edit</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="event-card" style="text-align: center; color: #666;">
                    <h3>Tiada Acara Dijumpai</h3>
                    <p>Belum ada acara yang dicipta. <a href="add_events.php">Klik di sini untuk menambah acara baru</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Filter functionality
        function applyFilters() {
            const typeFilter = document.getElementById('filter_type').value;
            const visibilityFilter = document.getElementById('filter_visibility').value;
            const searchTerm = document.getElementById('search_event').value.toLowerCase();
            
            const eventCards = document.querySelectorAll('.event-card[data-type]');
            
            eventCards.forEach(card => {
                const cardType = card.getAttribute('data-type');
                const cardVisibility = card.getAttribute('data-visibility');
                const cardName = card.getAttribute('data-name');
                
                let show = true;
                
                if (typeFilter && cardType !== typeFilter) show = false;
                if (visibilityFilter && cardVisibility !== visibilityFilter) show = false;
                if (searchTerm && !cardName.includes(searchTerm)) show = false;
                
                card.style.display = show ? 'block' : 'none';
            });
        }

        // Add event listeners
        document.getElementById('filter_type').addEventListener('change', applyFilters);
        document.getElementById('filter_visibility').addEventListener('change', applyFilters);
        document.getElementById('search_event').addEventListener('input', applyFilters);
    </script>
</body>

</html>
