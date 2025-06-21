<?php
require_once '../includes/session_check.php';
include '../config/connect.php';
include '../includes/header.php';

// Access control - only teachers and admins can add cocurricular profiles
if (!isset($_SESSION['user_ic']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    header("Location: ../auth/login.php?expired=true");
    exit;
}

$student_ic = null;
$student_info = null;

// For teachers, get student_ic from URL and verify access
if ($_SESSION['user_role'] === 'teacher') {
    if (!isset($_GET['student_ic'])) {
        echo "<script>alert('Student IC not provided.'); window.location.href='studentList.php';</script>";
        exit;
    }
    
    $teacher_ic = $_SESSION['user_ic'];
    $student_ic = $_GET['student_ic'];
    
    // Verify the student is in teacher's class
    $query = "SELECT s.*, c.class_name FROM student s 
              JOIN class c ON s.student_class = c.class_id 
              WHERE s.student_ic = ? AND c.head_teacher = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $student_ic, $teacher_ic);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<script>alert('Akses ditolak. Anda hanya boleh menguruskan murid dalam kelas anda.'); window.location.href='studentList.php';</script>";
        exit;
    }
    
    $student_info = $result->fetch_assoc();
} else {
    // For admin, they can select any student (implement if needed)
    echo "<script>alert('Admin access not yet implemented for this page.'); window.location.href='../admin/admin_dashboard.php';</script>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberships = [];
    
    // Process each category
    $categories = ['uniform_bodies', 'sports', 'clubs_associations', 'others'];
    
    foreach ($categories as $category) {
        if (!empty($_POST[$category]) && $_POST[$category] !== '') {
            $group_id = $_POST[$category];
            $role = $_POST[$category . '_role'] ?? 'member';
            
            // Check if membership already exists
            $check_query = "SELECT student_ic FROM student_club_membership WHERE student_ic = ? AND group_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("si", $student_ic, $group_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                // Insert new membership
                $insert_query = "INSERT INTO student_club_membership (student_ic, group_id, membership_role) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("sis", $student_ic, $group_id, $role);
                
                if ($insert_stmt->execute()) {
                    $memberships[] = "Added to group ID: $group_id with role: $role";
                } else {
                    $error_message = "Error adding membership: " . $insert_stmt->error;
                }
            } else {
                // Update existing membership
                $update_query = "UPDATE student_club_membership SET membership_role = ? WHERE student_ic = ? AND group_id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssi", $role, $student_ic, $group_id);
                
                if ($update_stmt->execute()) {
                    $memberships[] = "Updated role for group ID: $group_id to: $role";
                } else {
                    $error_message = "Error updating membership: " . $update_stmt->error;
                }
            }
        }
    }
    
    if (!empty($memberships)) {
        $success_message = "Profil kokurikulum berjaya dikemas kini!";
    }
}

// Get current student memberships
$current_memberships = [];
$membership_query = "SELECT scm.*, cg.group_name, cg.group_type 
                    FROM student_club_membership scm 
                    JOIN cocurricular_groups cg ON scm.group_id = cg.group_id 
                    WHERE scm.student_ic = ?";
$stmt = $conn->prepare($membership_query);
$stmt->bind_param("s", $student_ic);
$stmt->execute();
$membership_result = $stmt->get_result();

while ($membership = $membership_result->fetch_assoc()) {
    $current_memberships[$membership['group_type']] = [
        'group_id' => $membership['group_id'],
        'group_name' => $membership['group_name'],
        'role' => $membership['membership_role']
    ];
}

// Get available cocurricular groups by type
$groups_by_type = [];
$groups_query = "SELECT group_id, group_name, group_type FROM cocurricular_groups ORDER BY group_type, group_name";
$groups_result = mysqli_query($conn, $groups_query);

while ($group = mysqli_fetch_assoc($groups_result)) {
    $groups_by_type[$group['group_type']][] = $group;
}

// Role options
$role_options = [
    'member' => 'Ahli',
    'president' => 'Pengerusi',
    'vice_president' => 'Naib Pengerusi',
    'secretary' => 'Setiausaha',
    'vice_secretary' => 'Naib Setiausaha',
    'treasurer' => 'Bendahari',
    'vice_treasurer' => 'Naib Bendahari',
    'exco_y6' => 'Exco Tahun 6',
    'exco_y5' => 'Exco Tahun 5',
    'exco_y4' => 'Exco Tahun 4'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Profil Kokurikulum - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../assets/css/header&bg.css" />
    <link rel="stylesheet" href="../assets/css/cocurricular.css" />
    <link rel="stylesheet" href="../assets/css/button.css" />
    <link rel="stylesheet" href="../assets/css/form.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
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
                if (isset($_SESSION['user_role'])) {
                    if ($_SESSION['user_role'] === 'admin') {
                        echo '<span class="admin-text">' . strtoupper($_SESSION['admin_name'] ?? 'ADMIN') . '</span><br>';
                    } elseif ($_SESSION['user_role'] === 'teacher') {
                        // Get teacher name
                        $teacher_sql = "SELECT teacher_fname FROM teacher WHERE teacher_ic = '{$_SESSION['user_ic']}'";
                        $teacher_result = mysqli_query($conn, $teacher_sql);
                        $teacher_data = mysqli_fetch_assoc($teacher_result);
                        echo '<span class="admin-text">' . strtoupper($teacher_data['teacher_fname']) . '</span><br>';
                    }
                }
                ?>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <button onclick="location.href='../forms/approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
                <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
                notifications
                </span>
            </button>
        </div>
    </header>

    <div class="container">
        <h1 class="profile-title">TAMBAH PROFIL KOKURIKULUM</h1>
        
        <?php if (isset($success_message)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="btn-yellow" style="margin-bottom: 20px;">
            <a href="viewstudentCocurricular.php?student_ic=<?php echo urlencode($student_ic); ?>">← Kembali ke Profil Kokurikulum</a>
        </div>

        <section class="card-section">
            <div class="card left-card">
                <h3>Maklumat Pelajar</h3>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($student_info['student_fname']); ?></p>
                <p><strong>IC:</strong> <?php echo htmlspecialchars($student_info['student_ic']); ?></p>
                <p><strong>Kelas:</strong> <?php echo htmlspecialchars($student_info['class_name']); ?></p>
                <p><strong>Tahun:</strong> <?php echo date('Y'); ?></p>
            </div>

            <div class="card right-card">
                <h3>Kokurikulum Semasa</h3>
                <?php
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
                ?>
                <ul>
                    <li><strong>Unit Beruniform:</strong> <?php echo isset($current_memberships['uniform_bodies']) ? $current_memberships['uniform_bodies']['group_name'] . ' (' . ($role_labels[$current_memberships['uniform_bodies']['role']] ?? 'Ahli') . ')' : 'Tiada'; ?></li>
                    <li><strong>Sukan:</strong> <?php echo isset($current_memberships['sports']) ? $current_memberships['sports']['group_name'] . ' (' . ($role_labels[$current_memberships['sports']['role']] ?? 'Ahli') . ')' : 'Tiada'; ?></li>
                    <li><strong>Kelab & Persatuan:</strong> <?php echo isset($current_memberships['clubs_associations']) ? $current_memberships['clubs_associations']['group_name'] . ' (' . ($role_labels[$current_memberships['clubs_associations']['role']] ?? 'Ahli') . ')' : 'Tiada'; ?></li>
                    <li><strong>Lain-lain:</strong> <?php echo isset($current_memberships['others']) ? $current_memberships['others']['group_name'] . ' (' . ($role_labels[$current_memberships['others']['role']] ?? 'Ahli') . ')' : 'Tiada'; ?></li>
                </ul>
            </div>
        </section>

        <div class="card">
            <h3>Kemaskini Profil Kokurikulum</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="uniform_bodies"><strong>Unit Beruniform:</strong></label>
                    <select name="uniform_bodies" id="uniform_bodies">
                        <option value="">-- Pilih Unit Beruniform --</option>
                        <?php if (isset($groups_by_type['uniform_bodies'])): ?>
                            <?php foreach ($groups_by_type['uniform_bodies'] as $group): ?>
                                <option value="<?php echo $group['group_id']; ?>" 
                                    <?php echo (isset($current_memberships['uniform_bodies']) && $current_memberships['uniform_bodies']['group_id'] == $group['group_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <label for="uniform_bodies_role"><strong>Jawatan:</strong></label>
                    <select name="uniform_bodies_role" id="uniform_bodies_role">
                        <?php foreach ($role_options as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                <?php echo (isset($current_memberships['uniform_bodies']) && $current_memberships['uniform_bodies']['role'] == $value) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sports"><strong>Sukan:</strong></label>
                    <select name="sports" id="sports">
                        <option value="">-- Pilih Sukan --</option>
                        <?php if (isset($groups_by_type['sports'])): ?>
                            <?php foreach ($groups_by_type['sports'] as $group): ?>
                                <option value="<?php echo $group['group_id']; ?>" 
                                    <?php echo (isset($current_memberships['sports']) && $current_memberships['sports']['group_id'] == $group['group_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <label for="sports_role"><strong>Jawatan:</strong></label>
                    <select name="sports_role" id="sports_role">
                        <?php foreach ($role_options as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                <?php echo (isset($current_memberships['sports']) && $current_memberships['sports']['role'] == $value) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="clubs_associations"><strong>Kelab & Persatuan:</strong></label>
                    <select name="clubs_associations" id="clubs_associations">
                        <option value="">-- Pilih Kelab & Persatuan --</option>
                        <?php if (isset($groups_by_type['clubs_associations'])): ?>
                            <?php foreach ($groups_by_type['clubs_associations'] as $group): ?>
                                <option value="<?php echo $group['group_id']; ?>" 
                                    <?php echo (isset($current_memberships['clubs_associations']) && $current_memberships['clubs_associations']['group_id'] == $group['group_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <label for="clubs_associations_role"><strong>Jawatan:</strong></label>
                    <select name="clubs_associations_role" id="clubs_associations_role">
                        <?php foreach ($role_options as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                <?php echo (isset($current_memberships['clubs_associations']) && $current_memberships['clubs_associations']['role'] == $value) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="others"><strong>Ekstra Kokurikulum (Lain-lain):</strong></label>
                    <select name="others" id="others">
                        <option value="">-- Pilih Aktiviti Lain --</option>
                        <?php if (isset($groups_by_type['others'])): ?>
                            <?php foreach ($groups_by_type['others'] as $group): ?>
                                <option value="<?php echo $group['group_id']; ?>" 
                                    <?php echo (isset($current_memberships['others']) && $current_memberships['others']['group_id'] == $group['group_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <label for="others_role"><strong>Jawatan:</strong></label>
                    <select name="others_role" id="others_role">
                        <?php foreach ($role_options as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                <?php echo (isset($current_memberships['others']) && $current_memberships['others']['role'] == $value) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn-green" style="padding: 12px 30px; font-size: 16px;">
                        Simpan Profil Kokurikulum
                    </button>
                    <button type="button" class="btn-yellow" style="padding: 12px 30px; font-size: 16px; margin-left: 15px;" 
                            onclick="location.href='viewstudentCocurricular.php?student_ic=<?php echo urlencode($student_ic); ?>'">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .form-group {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #064789;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #064789;
            font-weight: bold;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 15px;
            background: white;
        }

        .form-group select:focus {
            border-color: #064789;
            outline: none;
            box-shadow: 0 0 5px rgba(6, 71, 137, 0.3);
        }

        .card h3 {
            color: #064789;
            border-bottom: 2px solid #064789;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .card ul {
            list-style: none;
            padding: 0;
        }

        .card ul li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .card ul li:last-child {
            border-bottom: none;
        }

        .btn-green {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-green:hover {
            background-color: #218838;
        }
    </style>
</body>

</html>
