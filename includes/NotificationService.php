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
        
        $title = "Status Borangg Aktiviti Dikemaskini";
        
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
}
?>
