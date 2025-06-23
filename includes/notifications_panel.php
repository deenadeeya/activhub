<?php
// notifications_panel.php - Enhanced notification dropdown
require_once __DIR__ . '/NotificationService.php';

if (!isset($conn) || !isset($_SESSION['user_ic'])) {
    return;
}

$notificationService = new NotificationService($conn);
$user_ic = $_SESSION['user_ic'];

// Get recent notifications and unread count
$recent_notifications = $notificationService->getUserNotifications($user_ic, 5);
$unread_count = $notificationService->getUnreadCount($user_ic);

// Clean up expired notifications
$notificationService->cleanupExpiredNotifications();
?>

<style>
.notification-dropdown {
    position: relative;
    display: inline-block;
}

.notification-panel {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    width: 320px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    border: 1px solid #e0e0e0;
}

.notification-header {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.notification-header h4 {
    margin: 0;
    color: #333;
    font-size: 16px;
}

.notification-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.notification-title {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 4px;
}

.notification-message {
    color: #666;
    font-size: 13px;
    line-height: 1.4;
}

.notification-time {
    color: #999;
    font-size: 11px;
    margin-top: 4px;
}

.notification-footer {
    padding: 12px 20px;
    text-align: center;
    border-top: 1px solid #f0f0f0;
}

.notification-footer a {
    color: #2196f3;
    text-decoration: none;
    font-size: 13px;
}

.notification-actions {
    padding: 10px 20px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
}

.mark-all-read {
    background: #2196f3;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.empty-notifications {
    padding: 30px 20px;
    text-align: center;
    color: #999;
    font-size: 14px;
}

.notification-bell {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 4px 7px;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
}

@media (max-width: 480px) {
    .notification-panel {
        width: 280px;
        right: -20px;
    }
}
</style>

<div class="notification-dropdown">    <?php
    // Determine correct path to notifications.php based on current location and user role
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $notifications_path = '';
    
    if (isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'student') {
            if ($current_dir === 'student') {
                $notifications_path = 'notifications.php';
            } else {
                $notifications_path = '../student/notifications.php';
            }
        } elseif ($_SESSION['user_role'] === 'teacher') {
            if ($current_dir === 'teacher') {
                $notifications_path = 'notifications.php';
            } else {
                $notifications_path = '../teacher/notifications.php';
            }
        }
    }
    ?>
    
    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'student' || $_SESSION['user_role'] === 'teacher')): ?>
    <!-- Enhanced notification bell for students and teachers -->
    <button class="notification-bell" onclick="toggleNotifications()" ondblclick="goToNotifications()" title="Klik untuk dropdown, double-click untuk halaman pemberitahuan">
        <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
            notifications
        </span>
        <?php if ($unread_count > 0): ?>
            <span class="notification-badge"><?= $unread_count > 99 ? '99+' : $unread_count ?></span>
        <?php endif; ?>
    </button>
    <?php else: ?>
    <!-- Default notification bell for admins -->
    <button class="notification-bell" onclick="toggleNotifications()">
        <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
            notifications
        </span>
        <?php if ($unread_count > 0): ?>
            <span class="notification-badge"><?= $unread_count > 99 ? '99+' : $unread_count ?></span>
        <?php endif; ?>
    </button>
    <?php endif; ?>

    <div class="notification-panel" id="notificationPanel">
        <div class="notification-header">
            <h4>Pemberitahuan</h4>
        </div>

        <?php if ($unread_count > 0): ?>
        <div class="notification-actions">
            <button class="mark-all-read" onclick="markAllAsRead()">Tandakan Semua Dibaca</button>
        </div>
        <?php endif; ?>

        <div class="notification-list">
            <?php if (empty($recent_notifications)): ?>
                <div class="empty-notifications">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #ddd;">notifications_off</span>
                    <p>Tiada pemberitahuan</p>
                </div>
            <?php else: ?>                <?php foreach ($recent_notifications as $notification): ?>
                    <?php
                    // Determine redirect URL based on notification type and user role
                    $redirect_url = '#';
                    
                    if (isset($_SESSION['user_role'])) {
                        if ($_SESSION['user_role'] === 'student') {
                            // Student notification routing
                            if ($notification['type'] === 'activity' || $notification['type'] === 'activity_submission' || $notification['type'] === 'activity_resubmission') {
                                $redirect_url = ($current_dir === 'student') ? 'student_formhistory.php' : '../student/student_formhistory.php';
                            } elseif ($notification['type'] === 'event' || $notification['type'] === 'registration' || $notification['type'] === 'deadline') {
                                if ($notification['related_id']) {
                                    $redirect_url = ($current_dir === 'student') ? '../events/register_event.php?event_id=' . $notification['related_id'] : '../events/register_event.php?event_id=' . $notification['related_id'];
                                } else {
                                    $redirect_url = ($current_dir === 'student') ? 'student_events.php' : '../student/student_events.php';
                                }
                            }                        } elseif ($_SESSION['user_role'] === 'teacher') {
                            // Teacher notification routing
                            if ($notification['type'] === 'activity_submission') {
                                // New submissions go to approve form
                                $redirect_url = '../forms/approve_form.php';
                            } elseif ($notification['type'] === 'activity_resubmission') {
                                // Resubmissions go to audit history to see the complete history
                                $redirect_url = '../forms/audit_history.php';
                            } elseif ($notification['type'] === 'activity' || $notification['type'] === 'activity_approved' || $notification['type'] === 'activity_rejected') {
                                $redirect_url = '../forms/audit_history.php';
                            } elseif ($notification['type'] === 'event' || $notification['type'] === 'registration' || $notification['type'] === 'deadline') {
                                $redirect_url = '../events/manage_events.php';
                            }
                        }
                    }
                    ?>                    <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>" 
                         onclick="window.location.href='<?= $redirect_url ?>?notification_id=<?= $notification['id'] ?>'">>
                        <div class="notification-title"><?= htmlspecialchars($notification['title']) ?></div>
                        <div class="notification-message"><?= htmlspecialchars($notification['message']) ?></div>
                        <div class="notification-time"><?= timeAgo($notification['created_at']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>        <?php if (!empty($recent_notifications)): ?>
        <div class="notification-footer">
            <?php
            // Determine correct path to notifications.php based on current location and user role
            $current_dir = basename(dirname($_SERVER['PHP_SELF']));
            $notifications_path = '';
            
            if (isset($_SESSION['user_role'])) {
                if ($_SESSION['user_role'] === 'student') {
                    if ($current_dir === 'student') {
                        $notifications_path = 'notifications.php';
                    } else {
                        $notifications_path = '../student/notifications.php';
                    }
                } elseif ($_SESSION['user_role'] === 'teacher') {
                    if ($current_dir === 'teacher') {
                        $notifications_path = 'notifications.php';
                    } else {
                        $notifications_path = '../teacher/notifications.php';
                    }
                } else {
                    // For admin or other roles, hide the link
                    $notifications_path = '';
                }
            }
            ?>
            <?php if ($notifications_path): ?>
                <a href="<?= $notifications_path ?>">Lihat Semua Pemberitahuan</a>
            <?php else: ?>
                <span style="color: #666; font-style: italic;">Tiada halaman pemberitahuan khusus</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleNotifications() {
    const panel = document.getElementById('notificationPanel');
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
}

function goToNotifications() {
    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'student' || $_SESSION['user_role'] === 'teacher')): ?>
    window.location.href = '<?= $notifications_path ?>';
    <?php endif; ?>
}

function handleNotificationClick(notificationId, relatedTable, relatedId) {
    // Mark as read
    markAsRead(notificationId);
    
    // Navigate based on notification type
    if (relatedTable === 'events' && relatedId) {
        // Could redirect to event details or registration page
        console.log('Event notification clicked:', relatedId);
    }
}

function handleSmartNotificationClick(notificationId, redirectUrl) {
    // Mark as read
    markAsRead(notificationId);
    
    // Navigate to the specified URL
    if (redirectUrl && redirectUrl !== '#') {
        window.location.href = redirectUrl;
    }
}

function markAsRead(notificationId) {
    fetch('../api/mark_notification_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({notification_id: notificationId})
    }).then(() => {
        // Refresh notification panel
        location.reload();
    });
}

function markAllAsRead() {
    fetch('../api/mark_notification_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({mark_all: true})
    }).then(() => {
        location.reload();
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.notification-dropdown');
    const panel = document.getElementById('notificationPanel');
    
    if (!dropdown.contains(event.target)) {
        panel.style.display = 'none';
    }
});
</script>

<?php
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Baru sahaja';
    if ($time < 3600) return floor($time/60) . ' minit lalu';
    if ($time < 86400) return floor($time/3600) . ' jam lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari lalu';
    
    return date('d M Y', strtotime($datetime));
}
?>
