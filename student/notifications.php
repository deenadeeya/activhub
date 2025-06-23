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
$notificationService = new NotificationService($conn);

// Handle mark as read action
if (isset($_GET['action']) && $_GET['action'] === 'mark_read' && isset($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    $notificationService->markAsRead($notification_id, $user_ic);
    header("Location: notifications.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get notifications
$all_notifications = $notificationService->getUserNotifications($user_ic, 1000); // Get all for counting
$total_notifications = count($all_notifications);
$total_pages = ceil($total_notifications / $per_page);

// Get notifications for current page
$notifications = array_slice($all_notifications, $offset, $per_page);

// Get student info
$sql = "
  SELECT s.*, c.class_year 
  FROM student s
  JOIN class c ON s.student_class = c.class_id
  WHERE s.student_ic = '$user_ic'
";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pemberitahuan - SRIAAWP ActivHub</title>  <link rel="stylesheet" href="../assets/css/header&bg.css" />
  <link rel="stylesheet" href="../assets/css/dash.css" />
  <link rel="stylesheet" href="../assets/css/button.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
  <style>
    .notifications-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
    }
      .notification-item {
      background: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-left: 4px solid #e0e0e0;
      transition: all 0.2s;
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      display: block;
    }
    
    .notification-item.unread {
      border-left-color: #2196f3;
      background: #f8fafe;
    }
    
    .notification-item:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .notification-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 10px;
    }
    
    .notification-title {
      font-weight: 600;
      color: #333;
      font-size: 16px;
      margin: 0;
    }
    
    .notification-time {
      color: #999;
      font-size: 12px;
      white-space: nowrap;
    }
    
    .notification-message {
      color: #666;
      line-height: 1.5;
      margin: 10px 0;
    }
    
    .notification-actions {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    
    .btn-mark-read {
      background: #2196f3;
      color: white;
      border: none;
      padding: 5px 12px;
      border-radius: 4px;
      font-size: 12px;
      cursor: pointer;
    }
    
    .pagination {
      text-align: center;
      margin-top: 30px;
    }
    
    .pagination a {
      display: inline-block;
      padding: 8px 12px;
      margin: 0 2px;
      background: #f5f5f5;
      color: #333;
      text-decoration: none;
      border-radius: 4px;
    }
    
    .pagination a.current {
      background: #2196f3;
      color: white;
    }
    
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #999;
    }
    
    .filter-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .filter-tab {
      padding: 10px 15px;
      background: none;
      border: none;
      cursor: pointer;
      border-bottom: 2px solid transparent;
    }
    
    .filter-tab.active {
      border-bottom-color: #2196f3;
      color: #2196f3;
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
        <span class="admin-text"><?= strtoupper($row['student_fname']) ?></span><br>
        <span class="welcome-text">Selamat Kembali!</span>
      </div>
      <?php include '../includes/notifications_panel.php'; ?>
    </div>
  </header>

  <div class="container">
    <div class="notifications-container">
      <h1>PEMBERITAHUAN</h1>
        <div style="margin-bottom: 20px;">
        <div class="btn-yellow"><a href="student_dashboard.php">← Kembali ke Dashboard</a></div>
      </div>

      <?php if (empty($notifications)): ?>
        <div class="empty-state">
          <span class="material-symbols-outlined" style="font-size: 64px; color: #ddd;">notifications_off</span>
          <h3>Tiada Pemberitahuan</h3>
          <p>Anda akan menerima pemberitahuan tentang acara baru, status aktiviti, dan pengumuman penting di sini.</p>
        </div>      <?php else: ?>
        <?php foreach ($notifications as $notification): ?>
          <?php
          // Determine redirect URL based on notification type
          $redirect_url = '#';
          if ($notification['type'] === 'activity' || $notification['type'] === 'activity_submission' || $notification['type'] === 'activity_resubmission') {
            $redirect_url = 'student_formhistory.php';
          } elseif ($notification['type'] === 'event' || $notification['type'] === 'registration' || $notification['type'] === 'deadline') {
            if ($notification['related_id']) {
              $redirect_url = '../events/register_event.php?event_id=' . $notification['related_id'];
            } else {
              $redirect_url = 'student_events.php';
            }
          }
          ?>
          
          <a href="<?= $redirect_url ?>" class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>" 
             onclick="markAsRead(<?= $notification['id'] ?>)">
            <div class="notification-header">
              <h3 class="notification-title"><?= htmlspecialchars($notification['title']) ?></h3>
              <span class="notification-time"><?= date('d M Y, H:i', strtotime($notification['created_at'])) ?></span>
            </div>
            
            <div class="notification-message">
              <?= htmlspecialchars($notification['message']) ?>
            </div>
            
            <?php if (!$notification['is_read']): ?>
              <div class="notification-actions" onclick="event.stopPropagation();">
                <button onclick="markAsReadOnly(<?= $notification['id'] ?>)" class="btn-mark-read">
                  Tandakan Dibaca
                </button>
              </div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>

        <?php if ($total_pages > 1): ?>
          <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <a href="notifications.php?page=<?= $i ?>" class="<?= $i === $page ? 'current' : '' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>    </div>
  </div>

  <script>
    function markAsRead(notificationId) {
      // Mark as read when notification is clicked
      fetch(`../api/mark_notification_read.php?id=${notificationId}`, {
        method: 'POST'
      });
    }

    function markAsReadOnly(notificationId) {
      // Mark as read without navigating
      event.preventDefault();
      event.stopPropagation();
      
      fetch(`../api/mark_notification_read.php?id=${notificationId}`, {
        method: 'POST'
      }).then(() => {
        location.reload();
      });
    }
  </script>
</body>
</html>
