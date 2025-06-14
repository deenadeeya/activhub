<?php
session_start();
include 'connect.php';
include 'header.php';

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

    // Handle approval action
    if (isset($_POST['approve']) && isset($_POST['activity_id'])) {
        $activity_id = $_POST['activity_id'];
        $currentDateTime = date('Y-m-d H:i:s');

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
        mysqli_query($conn, $updateQuery);
    }

    // Handle cancellation action
    if (isset($_POST['cancel']) && isset($_POST['activity_id'])) {
        $activity_id = $_POST['activity_id'];

        $updateQuery = "
            UPDATE cocu_activities SET approval_status = 'rejected', approved_by = NULL, approved_at = NULL,notification_read = 0 WHERE id = '$activity_id'
    ";
        mysqli_query($conn, $updateQuery);
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

    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
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
                <img src="../img/logo.png" alt="Logo" />
                <div class="logo-text">
                    <span>SRIAAWP ActivHub</span>
                    <?php include 'navlinks.php'; ?>
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
                                <button onclick="location.href='../approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
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
                                        <a href="<?php echo $row['cert_path']; ?>" target="_blank">[Sijil]</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="activity_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="approve">Luluskan</button>
                                        <button type="submit" name="cancel" style="margin-left: 10px;">Batalkan</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tiada borang yang belum diluluskan.</p>
            <?php endif ?>
        </div>
    <?php endif ?>
</body>

</html>