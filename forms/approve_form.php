<?php
require_once '../includes/session_check.php';
require_once '../includes/NotificationService.php';
include '../config/connect.php';
include '../includes/header.php';

if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    echo "Akses Ditolak. Please <a href='../login.php'>login again</a>.";
    exit;
}

$teacher_ic = $_SESSION['user_ic'];

// Get teacher's class from class table (since they're head teachers)
$classQuery = "SELECT class_id FROM class WHERE head_teacher = '$teacher_ic'";
$classResult = mysqli_query($conn, $classQuery);

if (!$classResult || mysqli_num_rows($classResult) == 0) {
    // Show modal and redirect instead of exiting
    $showModal = true;
    $modalMessage = "Akses ditolak. Anda bukan guru ketua kelas.";
    $redirectUrl = "../teacher/teacher_dashboard.php";
} else {
    $classData = mysqli_fetch_assoc($classResult);
    $teacherClass = $classData['class_id'];

    // Initialize message variables
    $success_message = '';
    $error_message = '';

    // Handle approval action
    if (isset($_POST['approve']) && isset($_POST['activity_id'])) {
        $activity_id = $_POST['activity_id'];
        $currentDateTime = date('Y-m-d H:i:s');

        // Get activity details before updating
        $activity_query = "SELECT ca.student_ic, ca.activity_name, s.student_fname FROM cocu_activities ca JOIN student s ON ca.student_ic = s.student_ic WHERE ca.id = '$activity_id'";
        $activity_result = mysqli_query($conn, $activity_query);
        $activity_data = mysqli_fetch_assoc($activity_result);

        // Update approval_status, approved_by, approved_at, reset notification_read
        $updateQuery = "
            UPDATE cocu_activities 
            SET 
                approval_status = 'approved',
                approved_by = '$teacher_ic',
                approved_at = '$currentDateTime',
                notification_read = 0
            WHERE id = '$activity_id'
        ";
        
        if (mysqli_query($conn, $updateQuery)) {
            $success_message = "Aktiviti '{$activity_data['activity_name']}' untuk pelajar {$activity_data['student_fname']} telah berjaya diluluskan!";
            
            // 🔥 NEW: Create modern notification for approval
            if ($activity_data) {
                $notificationService = new NotificationService($conn);
                $notificationService->notifyActivityStatusChange(
                    $activity_data['student_ic'], 
                    $activity_data['activity_name'], 
                    'approved'
                );
            }
        } else {
            $error_message = "Ralat berlaku semasa meluluskan aktiviti. Sila cuba lagi.";
        }
    }

    // Handle cancellation action
    if (isset($_POST['cancel']) && isset($_POST['activity_id']) && isset($_POST['rejection_remarks'])) {
        $activity_id = $_POST['activity_id'];
        $rejection_remarks = mysqli_real_escape_string($conn, $_POST['rejection_remarks']);
        $currentDateTime = date('Y-m-d H:i:s');

        // Get activity details before updating
        $activity_query = "SELECT ca.student_ic, ca.activity_name, s.student_fname FROM cocu_activities ca JOIN student s ON ca.student_ic = s.student_ic WHERE ca.id = '$activity_id'";
        $activity_result = mysqli_query($conn, $activity_query);
        $activity_data = mysqli_fetch_assoc($activity_result);

        $updateQuery = "
            UPDATE cocu_activities 
            SET 
                approval_status = 'rejected', 
                approved_by = '$teacher_ic', 
                approved_at = '$currentDateTime',
                rejection_remarks = '$rejection_remarks',
                notification_read = 0 
            WHERE id = '$activity_id'
        ";
        
        if (mysqli_query($conn, $updateQuery)) {
            $success_message = "Aktiviti '{$activity_data['activity_name']}' untuk pelajar {$activity_data['student_fname']} telah ditolak dengan sebab: {$rejection_remarks}";
            
            // 🔥 NEW: Create modern notification for rejection with custom message
            if ($activity_data) {
                $notificationService = new NotificationService($conn);
                
                // Create custom rejection notification with remarks
                $title = "Aktiviti Ditolak";
                $message = "Aktiviti '{$activity_data['activity_name']}' telah ditolak. Sebab: {$rejection_remarks}";
                
                $notificationService->createNotification(
                    $activity_data['student_ic'],
                    'student',
                    'activity',
                    $title,
                    $message,
                    $activity_id,
                    'cocu_activities'
                );
            }
        } else {
            $error_message = "Ralat berlaku semasa menolak aktiviti. Sila cuba lagi.";
        }
    }

    // Get unapproved applications for teacher's class
    $query = "
        SELECT a.id, s.student_fname, s.student_ic, a.activity_name, a.activity_date, a.activity_category, a.award, a.cert_path
        FROM cocu_activities a
        JOIN student s ON a.student_ic = s.student_ic
        WHERE s.student_class = '$teacherClass' AND a.approval_status = 'pending'
    ";

    $result = mysqli_query($conn, $query);
}

// Pending approval count
$pending_count = 0;
if (isset($teacherClass)) {
    $pending_query = "
            SELECT COUNT(*) AS total_pending
            FROM cocu_activities ca
            JOIN student s ON ca.student_ic = s.student_ic
            WHERE ca.approval_status = 'pending' AND s.student_class = ?
        ";
    $stmt = $conn->prepare($pending_query);
    $stmt->bind_param("s", $teacherClass);
    $stmt->execute();
    $pending_result = $stmt->get_result();
    $pending_data = $pending_result->fetch_assoc();
    $pending_count = $pending_data['total_pending'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Borang - SRIAAWP ActivHub</title>

    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link rel="stylesheet" href="../assets/css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
        /* Table styles */
        table {
        width: 80%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 1rem;
        font-family: Arial, sans-serif;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        table-layout: auto;
        }

        /* Header row */
        thead tr {
        background-color: #064789;
        color: #fff;
        text-align: left;
        }

        /* Hover effect */
        tbody tr {
        background-color: #ffffff; 
        }


        /* Table cells */
        th, td {
        padding: 12px 15px;
        border: 1px solid #ccc;
        }


        /* Hover effect */
        tbody tr:hover {
        background-color: #cbd2ff;
        cursor: pointer;
        }

        /* Responsive handling */
        @media screen and (max-width: 768px) {
        table {
            font-size: 0.9rem;
        }

        th, td {
            padding: 10px;
        }
        }

        .empty-space {
        height: 10px;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            text-align: center;
        }

        .modal-buttons {
            margin-top: 20px;
        }

        .modal-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-button:hover {
            background-color: #45a049;
        }

        /* Action button styles matching viewstudentCocurricular.php */
        .btn-green {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 0.9em;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-green:hover {
            background-color: #218838;
        }

        .btn-red {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 0.9em;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-left: 5px;
        }

        .btn-red:hover {
            background-color: #c82333;
        }

        /* Rejection modal styles */
        .rejection-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .rejection-modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }

        .rejection-modal h3 {
            color: #dc3545;
            margin-bottom: 15px;
        }

        .rejection-modal textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            resize: vertical;
        }

        .rejection-modal-buttons {
            margin-top: 15px;
            text-align: right;
        }

        .rejection-modal-buttons button {
            margin-left: 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .modal-cancel-btn:hover {
            background-color: #545b62;
        }

        .modal-reject-btn {
            background-color: #dc3545;
            color: white;
        }

        .modal-reject-btn:hover {
            background-color: #c82333;
        }

        /* Success and Error Message Styles */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
            font-size: 1em;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
            font-size: 1em;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
        }

        .message-icon {
            margin-right: 8px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php if (isset($showModal) && $showModal): ?>
        <!-- Modal -->
        <div id="errorModal" class="modal" style="display: block;">
            <div class="modal-content">
                <h2>Perhatian</h2>
                <p><?php echo $modalMessage; ?></p>
                <div class="modal-buttons">
                    <button class="modal-button" onclick="redirectToDashboard()">OK</button>
                </div>
            </div>
        </div>

        <script>
            function redirectToDashboard() {
                window.location.href = "<?php echo $redirectUrl; ?>";
            }
        </script>
    <?php else: ?>

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
                        } elseif ($_SESSION['user_role'] === 'teacher') {
                            // Get teacher name if needed
                            $teacherQuery = "SELECT teacher_fname FROM teacher WHERE teacher_ic = '$teacher_ic'";
                            $teacherResult = mysqli_query($conn, $teacherQuery);
                            $teacher = mysqli_fetch_assoc($teacherResult);
                            echo '<span class="admin-text">' . strtoupper($teacher['teacher_fname'] ?? 'TEACHER') . '</span><br>';
                        } elseif ($_SESSION['user_role'] === 'student') {
                            // Get student name if needed
                            $studentQuery = "SELECT student_fname FROM student WHERE student_ic = '$teacher_ic'";
                            $studentResult = mysqli_query($conn, $studentQuery);
                            $student = mysqli_fetch_assoc($studentResult);
                            echo '<span class="admin-text">' . strtoupper($student['student_fname'] ?? 'STUDENT') . '</span><br>';
                        }
                    }
                    ?>
                    <span class="welcome-text">Selamat Kembali!</span>
                </div>
                                <button onclick="location.href='approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
                    <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
                    notifications
                    </span>
                    <?php if ($pending_count > 0): ?>
                    <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 4px 7px; font-size: 12px;">
                        <?php echo $pending_count; ?>
                    </span>
                    <?php endif; ?>
                </button>
            </div>
        </header>

        <div class="container">
            <h1>Borang Pelajar Untuk Diluluskan</h1>
            <button class="btn-yellow"><a href="../teacher/teacher_dashboard.php">← Kembali ke Papan Pemuka</a></button>

            <!-- Success/Error Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <span class="message-icon">✅</span><?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <span class="message-icon">❌</span><?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table border="1" cellpadding="10">
                    <thead>
                        <tr>
                            <th>Nama Pelajar</th>
                            <th>IC</th>
                            <th>Nama Aktiviti</th>
                            <th>Tarikh Aktiviti</th>
                            <th>Kategori Aktiviti</th>
                            <th>Peringkat</th>
                            <th>Sijil</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_fname']) ?></td>
                                <td><?= htmlspecialchars($row['student_ic']) ?></td>
                                <td><?= htmlspecialchars($row['activity_name']) ?></td>
                                <td><?= htmlspecialchars($row['activity_date']) ?></td>
                                <td><?= htmlspecialchars($row['activity_category']) ?></td>
                                <td><?= htmlspecialchars($row['award']) ?></td>
                                <td>
                                    <?php if (!empty($row['cert_path'])): ?>
                                        <?php 
                                        // Handle certificate path - files are in assets/uploads/certificates/
                                        $cert_path = $row['cert_path'];
                                        $filename = basename($cert_path);
                                        
                                        // Try different possible paths including certificates subfolder
                                        $possible_paths = [
                                            '../assets/uploads/certificates/' . $filename,
                                            '../assets/uploads/' . $filename,
                                            '../assets/img/uploads/' . $filename,
                                            '../uploads/certificates/' . $filename,
                                            '../uploads/' . $filename,
                                            $cert_path // original path as fallback
                                        ];
                                        
                                        $found_path = null;
                                        foreach ($possible_paths as $test_path) {
                                            // For file existence check, convert relative path to absolute
                                            if (strpos($test_path, '../') === 0) {
                                                $absolute_path = __DIR__ . '/' . $test_path;
                                            } else {
                                                $absolute_path = __DIR__ . '/../' . $test_path;
                                            }
                                            
                                            if (file_exists($absolute_path)) {
                                                $found_path = $test_path;
                                                break;
                                            }
                                        }
                                        
                                        if ($found_path): ?>
                                            <a href="<?= htmlspecialchars($found_path) ?>" target="_blank">[Sijil]</a>
                                        <?php else: ?>
                                            <!-- Default to certificates folder -->
                                            <a href="<?= htmlspecialchars('../assets/uploads/certificates/' . $filename) ?>" target="_blank">[Sijil]</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Tiada
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Adakah anda pasti mahu meluluskan aktiviti ini?');">
                                        <input type="hidden" name="activity_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="approve" class="btn-green">Luluskan</button>
                                    </form>
                                    <button type="button" class="btn-red" onclick="openRejectionModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['student_fname']) ?>', '<?= htmlspecialchars($row['activity_name']) ?>')">Batalkan</button>
                                </td>
                            </tr>
                        <?php endwhile ?>
                    </tbody>
                </table>
            <?php else: ?>
                <?php if (!empty($success_message) || !empty($error_message)): ?>
                    <!-- Show message even when no pending forms -->
                <?php else: ?>
                    <p>Tiada borang yang belum diluluskan.</p>
                <?php endif; ?>
            <?php endif ?>
        </div>

        <!-- Rejection Modal -->
        <div id="rejectionModal" class="rejection-modal">
            <div class="rejection-modal-content">
                <h3>Sebab Penolakan</h3>
                <p>Pelajar: <span id="modalStudentName"></span></p>
                <p>Aktiviti: <span id="modalActivityName"></span></p>
                <form id="rejectionForm" method="post">
                    <input type="hidden" id="modalActivityId" name="activity_id" value="">
                    <input type="hidden" name="cancel" value="1">
                    <label for="rejectionRemarks"><strong>Catatan Penolakan:</strong></label>
                    <textarea id="rejectionRemarks" name="rejection_remarks" placeholder="Sila nyatakan sebab penolakan aktiviti ini..." required></textarea>
                    <div class="rejection-modal-buttons">
                        <button type="button" class="modal-cancel-btn" onclick="closeRejectionModal()">Batal</button>
                        <button type="submit" class="modal-reject-btn">Tolak Aktiviti</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openRejectionModal(activityId, studentName, activityName) {
                document.getElementById('modalActivityId').value = activityId;
                document.getElementById('modalStudentName').textContent = studentName;
                document.getElementById('modalActivityName').textContent = activityName;
                document.getElementById('rejectionRemarks').value = '';
                document.getElementById('rejectionModal').style.display = 'block';
            }

            function closeRejectionModal() {
                document.getElementById('rejectionModal').style.display = 'none';
            }

            // Close modal when clicking outside of it
            window.onclick = function(event) {
                const modal = document.getElementById('rejectionModal');
                if (event.target === modal) {
                    closeRejectionModal();
                }
            }
        </script>
    <?php endif ?>
</body>

</html>