<?php
session_start();
include 'connect.php';
include 'header.php';

// Access control logic
$student_ic = null;
if (!isset($_SESSION['user_ic']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php?expired=true");
    exit;
}

if ($_SESSION['user_role'] === 'student') {
    $student_ic = $_SESSION['user_ic'];
} elseif ($_SESSION['user_role'] === 'teacher' && isset($_GET['student_ic'])) {
    // Check if the student is in the teacher's class
    $teacher_ic = $_SESSION['user_ic'];
    $student_ic = $_GET['student_ic'];
    $query = "SELECT s.student_ic FROM student s JOIN class c ON s.student_class = c.class_id WHERE s.student_ic = ? AND c.head_teacher = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $student_ic, $teacher_ic);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Not allowed
        echo "<script>alert('Akses ditolak. Anda hanya boleh melihat murid dalam kelas anda.');window.location.href='studentList.php';</script>";
        exit;
    }
} else {
    // Not allowed
    header("Location: login.php?expired=true");
    exit;
}

// Get student info
$query = "SELECT s.*, c.class_name, t.teacher_fname, t.teacher_email 
          FROM student s 
          JOIN class c ON s.student_class = c.class_id 
          JOIN teacher t ON c.head_teacher = t.teacher_ic 
          WHERE s.student_ic = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_ic);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cocu_year = $_POST['cocu_year'];
    $uniform_bodies = $_POST['uniform_bodies'];
    $uniform_bodies_role = $_POST['uniform_bodies_role'];
    $sports = $_POST['sports'];
    $sports_role = $_POST['sports_role'];
    $clubs_assoc = $_POST['clubs_assoc'];
    $clubs_assoc_role = $_POST['clubs_assoc_role'];
    $activity_others = $_POST['activity_others'];

    $sql = "INSERT INTO cocurricular (
        student_ic, cocu_year, uniform_bodies, uniform_bodies_role,
        sports, sports_role, clubs_assoc, clubs_assoc_role, activity_others
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssss",
        $student_ic,
        $cocu_year,
        $uniform_bodies,
        $uniform_bodies_role,
        $sports,
        $sports_role,
        $clubs_assoc,
        $clubs_assoc_role,
        $activity_others
    );

    if ($stmt->execute()) {
        $success_message = "Data saved successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}

// Get existing cocurricular data (latest entry)
$cocu_query = "SELECT * FROM cocurricular WHERE student_ic = ? ORDER BY cocu_year DESC LIMIT 1";
$stmt = $conn->prepare($cocu_query);

if ($stmt) {
    $stmt->bind_param("s", $student_ic);
    $stmt->execute();
    $cocu_result = $stmt->get_result();
    $existing_cocu = $cocu_result->fetch_assoc();
} else {
    // Optional: Log or display an error message for debugging
    $error_message = "Database error: " . $conn->error;
}

// Pending Notification
$query = "
  SELECT COUNT(*) AS pending_count 
  FROM cocu_activities 
  WHERE student_ic = '$student_ic' 
    AND approval_status IN ('pending', 'approved', 'rejected')
    AND notification_read = 0
";

$result = mysqli_query($conn, $query);
$pending_count = 0;

if ($result && $row_pending = mysqli_fetch_assoc($result)) {
  $pending_count = $row_pending['pending_count'];
}

//Get existing corruciular activities
$activity_query = "SELECT * FROM cocu_activities WHERE student_ic = ? AND approval_status = 'approved' ORDER BY activity_date DESC";
$stmt = $conn->prepare($activity_query);
$stmt->bind_param("s", $student_ic);
$stmt->execute();
$activity_result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kokurikulum Murid - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/cocurricular.css" />
    <link rel="stylesheet" href="css/button.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
</head>

<body>
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
                    } elseif ($_SESSION['user_role'] === 'teacher' && !empty($teacher['teacher_fname'])) {
                        echo '<span class="admin-text">' . strtoupper($teacher['teacher_fname']) . '</span><br>';
                    } elseif ($_SESSION['user_role'] === 'student' && !empty($student['student_fname'])) {
                        echo '<span class="admin-text">' . strtoupper($student['student_fname']) . '</span><br>';
                    }
                }
                ?>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <?php
        // Replace with your actual notification count variable
        $notif_count = $pending_count;
        ?>

        <button onclick="location.href='student_formhistory.php'" style="position: relative; background: none; border: none; cursor: pointer;">
          <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
            notifications
          </span>

          <?php if ($notif_count > 0): ?>
            <span style="
              position: absolute;
              top: -5px;
              right: -5px;
              background: red;
              color: white;
              border-radius: 50%;
              padding: 4px 7px;
              font-size: 12px;
            ">
              <?php echo $notif_count; ?>
            </span>
          <?php endif; ?>
        </button>
        </div>
    </header>

    <div class="container">
        <button class="btn-yellow" onclick="window.print()" style="margin-bottom: 20px;">Cetak / Print</button>
        <style>
        @media print {
            header, .icon-section, .btn-yellow, nav, .output-card, .welcome-text {
                display: none !important;
            }
            body, .container {
                background: #fff !important;
                box-shadow: none !important;
            }
            .container {
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .card-section, .card {
                page-break-inside: avoid;
            }
            .profile-title.aktiviti-title {
                page-break-before: always !important;
            }
            table {
                width: 100% !important;
                font-size: 12px;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #000 !important;
                color: #000 !important;
            }
            input[readonly] {
                border: none;
                background: none;
                color: #000;
                font-weight: bold;
            }
            /* Hide certificate links, show [Sijil] text in print */
            a[href$='.pdf'], a[href$='.jpg'], a[href$='.jpeg'], a[href$='.png'], a[href*='cert_path'] {
                display: none !important;
            }
            .print-certificate-label {
                display: inline !important;
                font-weight: bold;
                color: #222;
            }
        }
        </style>
        <h1 class="profile-title">PROFIL KOKURIKULUM</h1>

        <?php if (isset($success_message)) echo "<p style='color:green;'>$success_message</p>"; ?>
        <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>

        <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'teacher'])): ?>
            <button class="btn-yellow" onClick="location.href='student_addcocuprofile.php';">TAMBAH PROFIL KOKURIKULUM</button>
        <?php endif; ?>
        <section class="card-section">
            <div class="card left-card">
                <h3>Tahun: <span><?php echo date("Y"); ?></span></h3>
                <h3>Nama: <span><?php echo $row['student_fname']; ?></span></h3>
                <p>Kelas: <span><?php echo $row['class_name']; ?></span></p>
            </div>

            <div class="card right-card">
                <?php
                // Use student_club_membership for all memberships
                $group_types = [
                    'uniform_bodies' => 'Unit Beruniform',
                    'sports' => 'Sukan',
                    'clubs_associations' => 'Kelab & Persatuan',
                    'others' => 'Ekstra Kokurikulum (Lain-lain)'
                ];
                $role_labels = [
                    'president' => 'Pengerusi',
                    'vice_president' => 'Naib Pengerusi',
                    'secretary' => 'Setiausaha',
                    'vice_secretary' => 'Naib Setiausaha',
                    'treasurer' => 'Bendahari',
                    'vice_treasurer' => 'Naib Bendahari',
                    'exco_y6' => 'Exco Tahun 6',
                    'exco_y5' => 'Exco Tahun 5',
                    'exco_y4' => 'Exco Tahun 4',
                    'member' => 'Ahli',
                    '' => 'Ahli'
                ];
                $student_assignments = [
                    'uniform_bodies' => [],
                    'sports' => [],
                    'clubs_associations' => [],
                    'others' => []
                ];
                $sql = "SELECT scm.*, cg.group_name, cg.group_type FROM student_club_membership scm JOIN cocurricular_groups cg ON scm.group_id = cg.group_id WHERE scm.student_ic = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $student_ic);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($rowm = $result->fetch_assoc()) {
                    $type = $rowm['group_type'];
                    $role = $rowm['membership_role'];
                    $role_label = isset($role_labels[$role]) ? $role_labels[$role] : ucfirst($role);
                    $student_assignments[$type][] = [
                        'group_name' => $rowm['group_name'],
                        'role' => $role_label
                    ];
                }
                function display_assignments($arr) {
                    if (empty($arr)) return '-';
                    $names = array_map(function($a) {
                        return $a['group_name'];
                    }, $arr);
                    return implode(', ', $names);
                }
                function display_roles($arr) {
                    if (empty($arr)) return '-';
                    $roles = array_map(function($a) {
                        return $a['role'];
                    }, $arr);
                    return implode(', ', $roles);
                }
                ?>
                <ul class="activity-list">
                    <li><label><strong>Tahun: </strong><input type="text" value="<?php echo date('Y'); ?>" readonly></label></li>
                    <li><label><strong>Unit Beruniform: </strong><input type="text" value="<?php echo display_assignments($student_assignments['uniform_bodies'] ?? []); ?>" readonly></label></li>
                    <li><label><strong>Jawatan: </strong><input type="text" value="<?php echo display_roles($student_assignments['uniform_bodies'] ?? []); ?>" readonly></label></li>
                    <li><label><strong>Sukan: </strong><input type="text" value="<?php echo display_assignments($student_assignments['sports'] ?? []); ?>" readonly></label></li>
                    <li><label><strong>Jawatan: </strong><input type="text" value="<?php echo display_roles($student_assignments['sports'] ?? []); ?>" readonly></label></li>
                    <li><label><strong>Kelab & Persatuan: </strong><input type="text" value="<?php echo display_assignments($student_assignments['clubs_associations'] ?? []); ?>" readonly></label></li>
                    <li><label><strong>Jawatan: </strong><input type="text" value="<?php echo display_roles($student_assignments['clubs_associations'] ?? []); ?>" readonly></label></li>
                    <li><label><strong>Ekstra Kokurikulum: </strong><input type="text" value="<?php echo display_assignments($student_assignments['others'] ?? []); ?>" readonly></label></li>
                </ul>
            </div>
        </section>

        <?php if (isset($success_message)): ?>
            <div class="card output-card">
                <h3>Submitted Co-Curricular Info</h3>
                <ul>
                    <li><strong>Tahun:</strong> <?php echo htmlspecialchars($cocu_year); ?></li>
                    <li><strong>Unit Beruniform:</strong> <?php echo htmlspecialchars($uniform_bodies); ?></li>
                    <li><strong>Jawatan (Unit Beruniform):</strong> <?php echo htmlspecialchars($uniform_bodies_role); ?></li>
                    <li><strong>Sukan:</strong> <?php echo htmlspecialchars($sports); ?></li>
                    <li><strong>Jawatan (Sukan):</strong> <?php echo htmlspecialchars($sports_role); ?></li>
                    <li><strong>Kelab & Persatuan:</strong> <?php echo htmlspecialchars($clubs_assoc); ?></li>
                    <li><strong>Jawatan (Kelab):</strong> <?php echo htmlspecialchars($clubs_assoc_role); ?></li>
                    <li><strong>Ekstra Kokurikulum:</strong> <?php echo htmlspecialchars($activity_others); ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <h1 class="profile-title aktiviti-title">AKTIVITI KOKURIKULUM</h1>
        <button class="btn-yellow" onClick="location.href='student_cocuactivityform.php';">BORANG TAMBAH AKTIVITI KOKURIKULUM</button>




        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Tarikh</th>
                    <th>Nama Aktiviti</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Peringkat</th>
                    <th>Pencapaian</th>
                    <th>Sijil</th> <!-- New column for certificate -->
                </tr>
            </thead>
            <tbody>
                <?php if ($activity_result->num_rows > 0): ?>
                    <?php while ($activity = $activity_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['activity_date']); ?></td>
                            <td><?php echo htmlspecialchars($activity['activity_name']); ?></td>
                            <td><?php echo htmlspecialchars($activity['activity_category']); ?></td>
                            <td><?php echo htmlspecialchars($activity['activity_location']); ?></td>
                            <td><?php echo htmlspecialchars($activity['award']); ?></td>
                            <td><?php echo htmlspecialchars($activity['ach']); ?></td>
                            <td>
                                <?php if (!empty($activity['cert_path'])): ?>
                                    <a href="<?php echo $activity['cert_path']; ?>" target="_blank">[Sijil]</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Tiada aktiviti ditemui.</td>
                    </tr>
                <?php endif; ?>
            </tbody>

            </tbody>
        </table>

    </div>
</body>

</html>