<?php
session_start();
require_once 'connect.php';
include 'header.php';

if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php?expired=true");
    exit();
}

$teacher_ic = $_SESSION['user_ic'];

// Get teacher's class id for notification count
$teacher_class_id = null;
$sql_class_id = "SELECT class_id FROM class WHERE head_teacher = '$teacher_ic'";
$result_class_id = mysqli_query($conn, $sql_class_id);
if ($result_class_id && mysqli_num_rows($result_class_id) > 0) {
    $row_class_id = mysqli_fetch_assoc($result_class_id);
    $teacher_class_id = $row_class_id['class_id'];
}

// Check if the teacher is a head teacher of any class
$sql = "SELECT class.* FROM class INNER JOIN teacher ON class.head_teacher = teacher.teacher_ic WHERE teacher.teacher_ic = '$teacher_ic'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // Teacher is not a head teacher - show access denied modal
    $show_modal = true;
} else {
    $class = mysqli_fetch_assoc($result);
    $class_id = $class['class_id'];

    $sql_students = "SELECT student.*, class.class_name 
                    FROM student 
                    INNER JOIN class ON student.student_class = class.class_id 
                    WHERE student.student_class = '$class_id'";
    $result_students = mysqli_query($conn, $sql_students);
}

// Pending approval count
$pending_count = 0;
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student List - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../css/teacherList.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
        
</head>

<body>
    <!-- Access Denied Modal -->
    <?php if (isset($show_modal) && $show_modal): ?>
        <div id="accessDeniedModal" class="modal" style="display: block;">
            <div class="modal-content">
                <div class="modal-message">Akses ditolak. Anda bukan guru ketua kelas.</div>
                <button class="modal-button" onclick="window.location.href='teacher/teacher_dashboard.php'">OK</button>
            </div>
        </div>
    <?php endif; ?>

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
                        // Get teacher name
                        $teacher_sql = "SELECT teacher_fname FROM teacher WHERE teacher_ic = '$teacher_ic'";
                        $teacher_result = mysqli_query($conn, $teacher_sql);
                        $teacher_data = mysqli_fetch_assoc($teacher_result);
                        echo '<span class="admin-text">' . strtoupper($teacher_data['teacher_fname']) . '</span><br>';
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

    <?php if (!isset($show_modal)): ?>
        <div class="container">
            <div class="teacher-list-container">
                <div class="teacher-list-box">
                    <div class="title-bar">
                        <h2 class="color:white; ">Senarai Murid - <?php echo $class['class_name']; ?></h2>
                        <div class="button-group">
                            <button class="btn-yellow" onclick="window.location.href='student_add.php'">Tambah Pelajar Baru</button>
                            <button class="btn-red" onclick="location.href='teacher/teacher_dashboard.php'">Kembali</button>
                        </div>
                    </div>
                    
                    <?php
                    if (mysqli_num_rows($result_students) > 0) {
                        while ($row = mysqli_fetch_assoc($result_students)) { ?>
                            <div class="teacher-card" id="<?php echo $row["student_ic"]; ?>">
                                <p><strong><?php echo $row["student_fname"]; ?></strong><br>
                                    <span class="credentials">Nombor Kad Pengenalan:</span> <?php echo $row["student_ic"]; ?><br>
                                    <span class="credentials">Nombor Matric:</span> <?php echo $row["matrix"]; ?><br><br>
                                    <button class="edit-button" onclick="edit('<?php echo $row['student_ic']; ?>')">Edit</button>
                                    <button class="edit-button" style="margin-left:8px;" onclick="window.location.href='viewstudentCocurricular.php?student_ic=<?php echo $row['student_ic']; ?>'">View</button>
                            </div>
                        <?php }
                    } else {
                        ?>
                        <div class="teacher-card">
                            <p><strong>Tiada Murid Dalam Kelas Ini</strong><br>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function edit(id) {
            const data = {
                id: id
            };

            fetch('function/get_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById(id).innerHTML = result.message;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function save(id) {
            const name = document.getElementsByName("edit_name_" + id)[0].value;
            const password = document.getElementsByName("edit_password_" + id)[0].value;
            const dob = document.getElementsByName("student_dob_" + id)[0].value;
            const doe = document.getElementsByName("student_doe_" + id)[0].value;
            const gender = document.getElementsByName("gender_" + id)[0].value;
            const matrix = document.getElementsByName("matrix_" + id)[0].value;
            const contact_num = document.getElementsByName("contact_num_" + id)[0].value;

            const data = {
                id: id,
                name: name,
                password: password,
                dob: dob,
                doe: doe,
                gender: gender,
                matrix: matrix,
                contact_num: contact_num
            };

            fetch('function/student_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status == 1) {
                        alert("Berjaya Dikemaskini!");
                    } else {
                        alert("Tidak Berjaya Dikemaskini!");
                    }
                    document.getElementById(id).innerHTML = result.message;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function cancel(id) {
            fetch('function/student_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById(id).innerHTML = result.message;
                });
        }
    </script>
</body>

</html>