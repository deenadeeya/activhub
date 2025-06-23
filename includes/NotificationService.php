<?php
class NotificationService {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Create a new notification
     */
    public function createNotification($user_ic, $user_role, $type, $title, $message, $related_id = null, $related_table = null, $expires_at = null) {
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_ic, user_role, type, title, message, related_id, related_table, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $user_ic, $user_role, $type, $title, $message, $related_id, $related_table, $expires_at);
        return $stmt->execute();
    }
    
    /**
     * Get notifications for a specific user
     */
    public function getUserNotifications($user_ic, $limit = 10, $unread_only = false) {
        $sql = "SELECT * FROM notifications WHERE user_ic = ?";
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $user_ic, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount($user_ic) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_ic = ? AND is_read = 0");
        $stmt->bind_param("s", $user_ic);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notification_id, $user_ic) {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_ic = ?");
        $stmt->bind_param("is", $notification_id, $user_ic);
        return $stmt->execute();
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($user_ic) {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_ic = ? AND is_read = 0");
        $stmt->bind_param("s", $user_ic);
        return $stmt->execute();
    }
      /**
     * Event Notifications - Enhanced with club membership support
     */
    public function notifyNewEvent($event_id, $event_name, $visibility = 'public', $group_id = null, $eligible_years = null) {
        $students = [];
        
        if ($visibility === 'club_only' && $group_id) {            // Only notify club members for club-only events
            $students_query = "
                SELECT DISTINCT s.student_ic 
                FROM student s 
                JOIN class c ON s.student_class = c.class_id
                JOIN student_club_membership scm ON s.student_ic = scm.student_ic
                WHERE scm.group_id = ?
            ";
            
            // Add eligible years filter if specified
            if ($eligible_years) {
                $years_array = explode(',', $eligible_years);
                $placeholders = str_repeat('?,', count($years_array) - 1) . '?';
                $students_query .= " AND c.class_year IN ($placeholders)";
                
                $params = array_merge([$group_id], $years_array);
                $types = 'i' . str_repeat('s', count($years_array));
                
                $stmt = $this->conn->prepare($students_query);
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt = $this->conn->prepare($students_query);
                $stmt->bind_param('i', $group_id);
            }
            
            $stmt->execute();
            $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Get club name for personalized message
            $club_query = "SELECT group_name FROM cocurricular_groups WHERE group_id = ?";
            $club_stmt = $this->conn->prepare($club_query);
            $club_stmt->bind_param('i', $group_id);
            $club_stmt->execute();
            $club_result = $club_stmt->get_result()->fetch_assoc();
            $club_name = $club_result['group_name'] ?? 'Kelab Anda';
            
            $title = "Acara Kelab Baru: {$club_name}";
            $message = "Acara '{$event_name}' telah dijadualkan untuk ahli {$club_name}. Kehadiran anda diperlukan!";
            
        } else {
            // Public events - notify all eligible students
            $students_query = "
                SELECT s.student_ic 
                FROM student s 
                JOIN class c ON s.student_class = c.class_id
            ";
            
            if ($eligible_years) {
                $years_array = explode(',', $eligible_years);
                $placeholders = str_repeat('?,', count($years_array) - 1) . '?';
                $students_query .= " WHERE c.class_year IN ($placeholders)";
                $stmt = $this->conn->prepare($students_query);
                $stmt->bind_param(str_repeat('s', count($years_array)), ...$years_array);
            } else {
                $stmt = $this->conn->prepare($students_query);
            }
            
            $stmt->execute();
            $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $title = "Acara Baru Tersedia";
            $message = "Acara '{$event_name}' kini terbuka untuk pendaftaran. Klik untuk melihat butiran dan mendaftar.";
        }
        
        // Send notifications to all eligible students
        foreach ($students as $student) {
            $this->createNotification(
                $student['student_ic'], 
                'student', 
                'event', 
                $title, 
                $message, 
                $event_id, 
                'events'
            );
        }
        
        return count($students); // Return number of students notified
    }
    
    public function notifyEventRegistration($user_ic, $event_name) {
        $title = "Pendaftaran Acara Berjaya";
        $message = "Anda telah berjaya mendaftar untuk acara '{$event_name}'. Terima kasih!";
        
        $this->createNotification($user_ic, 'student', 'registration', $title, $message);
    }    public function notifyActivityStatusChange($user_ic, $activity_name, $status, $custom_message = null) {
        $status_text = [
            'approved' => 'diluluskan',
            'rejected' => 'ditolak',
            'pending' => 'dalam semakan'
        ];
        
        $title = "Status Borang Aktiviti Dikemaskini";
        
        if (!empty($custom_message)) {
            // Use custom message (like rejection with reasons)
            $message = $custom_message;
        } else {
            // Use default message
            $message = "Borang Aktiviti '{$activity_name}' telah {$status_text[$status]}.";
        }
        
        $this->createNotification($user_ic, 'student', 'activity', $title, $message);
    }
    
    public function notifyRegistrationDeadline($event_id, $event_name, $deadline_date) {
        // Get students who haven't registered yet
        $unregistered_query = "
            SELECT DISTINCT s.student_ic 
            FROM student s
            WHERE s.student_ic NOT IN (
                SELECT er.student_ic 
                FROM event_registrations er 
                WHERE er.event_id = ?
            )
        ";
        
        $stmt = $this->conn->prepare($unregistered_query);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $title = "Peringatan: Tarikh Akhir Pendaftaran";
        $message = "Pendaftaran untuk acara '{$event_name}' akan ditutup pada {$deadline_date}. Daftar sekarang!";
        
        foreach ($students as $student) {
            $this->createNotification(
                $student['student_ic'], 
                'student', 
                'deadline', 
                $title, 
                $message, 
                $event_id, 
                'events',
                $deadline_date . ' 23:59:59'
            );
        }
    }
    
    /**
     * Clean up expired notifications
     */
    public function cleanupExpiredNotifications() {
        $stmt = $this->conn->prepare("DELETE FROM notifications WHERE expires_at IS NOT NULL AND expires_at < NOW()");
        return $stmt->execute();
    }
    
    /**
     * Notify club members about mandatory events
     */
    public function notifyMandatoryClubEvent($event_id, $event_name, $group_id, $eligible_years = null) {
        // Get club members
        $members_query = "
            SELECT DISTINCT s.student_ic, s.student_fname
            FROM student s 
            JOIN class c ON s.student_class = c.class_id
            JOIN student_club_membership scm ON s.student_ic = scm.student_ic
            WHERE scm.group_id = ?
        ";
        
        // Add eligible years filter if specified
        if ($eligible_years) {
            $years_array = explode(',', $eligible_years);
            $placeholders = str_repeat('?,', count($years_array) - 1) . '?';
            $members_query .= " AND c.class_year IN ($placeholders)";
            
            $params = array_merge([$group_id], $years_array);
            $types = 'i' . str_repeat('s', count($years_array));
            
            $stmt = $this->conn->prepare($members_query);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->conn->prepare($members_query);
            $stmt->bind_param('i', $group_id);
        }
        
        $stmt->execute();
        $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get club name
        $club_query = "SELECT group_name FROM cocurricular_groups WHERE group_id = ?";
        $club_stmt = $this->conn->prepare($club_query);
        $club_stmt->bind_param('i', $group_id);
        $club_stmt->execute();
        $club_result = $club_stmt->get_result()->fetch_assoc();
        $club_name = $club_result['group_name'] ?? 'Kelab Anda';
        
        $title = "🚨 ACARA WAJIB: {$club_name}";
        $message = "PENTING: Acara '{$event_name}' adalah WAJIB untuk semua ahli {$club_name}. Kehadiran adalah DIWAJIBKAN!";
        
        // Send urgent notifications to all club members
        foreach ($members as $member) {
            $this->createNotification(
                $member['student_ic'], 
                'student', 
                'event', 
                $title, 
                $message, 
                $event_id, 
                'events'
            );
        }
        
        return count($members); // Return number of members notified
    }
    
    /**
     * Activity Form Notifications for Teachers
     */
    public function notifyTeacherActivitySubmission($teacher_ic, $student_ic, $activity_name, $activity_id, $is_resubmission = false) {
        // Get student name for personalized message
        $student_query = "SELECT student_fname FROM student WHERE student_ic = ?";
        $stmt = $this->conn->prepare($student_query);
        $stmt->bind_param("s", $student_ic);
        $stmt->execute();
        $student_result = $stmt->get_result()->fetch_assoc();
        $student_name = $student_result['student_fname'] ?? 'Murid';
        
        if ($is_resubmission) {
            $title = "Borang Aktiviti Dihantar Semula";
            $message = "{$student_name} telah menghantar semula borang aktiviti '{$activity_name}' untuk semakan semula.";
            $type = 'activity_resubmission';
        } else {
            $title = "Borang Aktiviti Baru";
            $message = "{$student_name} telah menghantar borang aktiviti '{$activity_name}' untuk kelulusan anda.";
            $type = 'activity_submission';
        }
        
        $this->createNotification($teacher_ic, 'teacher', $type, $title, $message, $activity_id, 'cocu_activities');
    }
    
    /**
     * Admin Notification Methods
     */
    
    /**
     * Get admin notification count for all pending items
     */
    public function getAdminNotificationCount($admin_ic) {
        $count = 0;
        
        // 1. All pending activity approvals across all classes
        $pending_activities = "SELECT COUNT(*) as count FROM cocu_activities WHERE approval_status = 'pending'";
        $result = $this->conn->query($pending_activities);
        if ($result) {
            $count += $result->fetch_assoc()['count'];
        }
        
        // 2. Upcoming event deadlines (within 7 days)
        $upcoming_deadlines = "SELECT COUNT(*) as count FROM events 
                              WHERE registration_deadline >= CURDATE() 
                              AND registration_deadline <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $result = $this->conn->query($upcoming_deadlines);
        if ($result) {
            $count += $result->fetch_assoc()['count'];
        }
          // 3. Teachers with pending items for more than 3 days
        $inactive_teachers = "SELECT COUNT(DISTINCT t.teacher_ic) as count 
                             FROM teacher t
                             JOIN class c ON t.teacher_ic = c.head_teacher
                             JOIN student s ON c.class_id = s.student_class
                             JOIN cocu_activities ca ON s.student_ic = ca.student_ic
                             WHERE ca.approval_status = 'pending' 
                             AND ca.created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)";
        $result = $this->conn->query($inactive_teachers);
        if ($result) {
            $count += $result->fetch_assoc()['count'];
        }
        
        // Add unread admin notifications
        $unread_notifications = $this->getUnreadCount($admin_ic);
        
        return $count + $unread_notifications;
    }
    
    /**
     * Generate admin notifications for pending approvals
     */
    public function generateAdminNotifications($admin_ic) {
        // Clear old admin notifications of these types
        $this->conn->query("DELETE FROM notifications 
                           WHERE user_ic = '$admin_ic' 
                           AND user_role = 'admin' 
                           AND type IN ('admin_pending', 'admin_deadline', 'admin_teacher_alert')
                           AND created_at <= DATE_SUB(NOW(), INTERVAL 1 DAY)");
          // 1. Pending approvals notification
        $pending_query = "SELECT COUNT(*) as total_pending FROM cocu_activities WHERE approval_status = 'pending'";
        $result = $this->conn->query($pending_query);
        if ($result) {
            $total_pending = $result->fetch_assoc()['total_pending'];
            
            if ($total_pending > 0) {
                $title = "Kelulusan Tertunda: {$total_pending} Aktiviti";
                $message = "Terdapat {$total_pending} permohonan aktiviti kokurikulum menunggu kelulusan guru kelas.";
                $this->createNotification($admin_ic, 'admin', 'admin_pending', $title, $message, null, 'cocu_activities');
            }
        }
        
        // 2. Event deadline notifications
        $deadline_query = "SELECT event_id, event_name, registration_deadline 
                          FROM events 
                          WHERE registration_deadline >= CURDATE() 
                          AND registration_deadline <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                          ORDER BY registration_deadline ASC";
        $deadline_result = $this->conn->query($deadline_query);
        
        if ($deadline_result && $deadline_result->num_rows > 0) {
            while ($event = $deadline_result->fetch_assoc()) {
                $days_left = floor((strtotime($event['registration_deadline']) - time()) / 86400);
                $title = "Tarikh Akhir Pendaftaran: {$event['event_name']}";
                $message = "Pendaftaran untuk '{$event['event_name']}' akan ditutup dalam {$days_left} hari.";
                $this->createNotification($admin_ic, 'admin', 'admin_deadline', $title, $message, $event['event_id'], 'events');
            }
        }
          // 3. Teacher inactivity alerts
        $inactive_query = "SELECT DISTINCT t.teacher_ic, t.teacher_fname, 
                          COUNT(ca.id) as pending_count,
                          MIN(ca.created_at) as oldest_submission
                          FROM teacher t
                          JOIN class c ON t.teacher_ic = c.head_teacher
                          JOIN student s ON c.class_id = s.student_class
                          JOIN cocu_activities ca ON s.student_ic = ca.student_ic
                          WHERE ca.approval_status = 'pending' 
                          AND ca.created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)
                          GROUP BY t.teacher_ic
                          HAVING pending_count > 0";
        
        $inactive_result = $this->conn->query($inactive_query);
        if ($inactive_result && $inactive_result->num_rows > 0) {            while ($teacher = $inactive_result->fetch_assoc()) {
                $days_pending = floor((time() - strtotime($teacher['oldest_submission'])) / 86400);
                $title = "Guru Tidak Aktif: {$teacher['teacher_fname']}";
                $message = "Guru {$teacher['teacher_fname']} mempunyai {$teacher['pending_count']} permohonan belum diproses selama {$days_pending} hari.";
                $this->createNotification($admin_ic, 'admin', 'admin_teacher_alert', $title, $message, $teacher['teacher_ic'], 'teacher');
            }
        }
    }
    
    /**
     * Create system alert notification for admin
     */
    public function createSystemAlert($admin_ic, $alert_type, $message_details) {
        $title = "Amaran Sistem: " . ucfirst($alert_type);
        $message = "Sistem mengesan isu: " . $message_details;
        return $this->createNotification($admin_ic, 'admin', 'system_alert', $title, $message);
    }
    
    /**
     * Check for system issues and create alerts
     */
    public function checkSystemHealth($admin_ic) {
        // Alert if too many pending activities (>20)
        $pending_query = "SELECT COUNT(*) as count FROM cocu_activities WHERE approval_status = 'pending'";
        $result = $this->conn->query($pending_query);
        $pending_count = $result->fetch_assoc()['count'];
        
        if ($pending_count > 20) {
            $this->createSystemAlert($admin_ic, "high_pending_load", "Terlalu banyak aktiviti menunggu kelulusan: {$pending_count} item");
        }        // Alert if events with very low registration (< 5 participants) near deadline
        $low_reg_query = "SELECT e.event_name, COUNT(er.student_ic) as participants
                         FROM events e
                         LEFT JOIN event_registrations er ON e.event_id = er.event_id
                         WHERE e.registration_deadline >= CURDATE() 
                         AND e.registration_deadline <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                         GROUP BY e.event_id, e.event_name
                         HAVING participants < 5";
        
        $low_reg_result = $this->conn->query($low_reg_query);
        if ($low_reg_result && $low_reg_result->num_rows > 0) {
            while ($event = $low_reg_result->fetch_assoc()) {
                $this->createSystemAlert($admin_ic, "low_event_registration", "Pendaftaran rendah untuk '{$event['event_name']}': {$event['participants']} peserta");
            }
        }
    }
}
?>
