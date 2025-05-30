<?php
session_start();
include 'connect.php';
include 'header.php';

if (!isset($_GET['group_id']) || !is_numeric($_GET['group_id'])) {
    echo "<script>alert('Invalid group ID.'); window.location.href='cocurricular_board.php';</script>";
    exit();
}

$group_id = intval($_GET['group_id']);

// Fetch group info
$group_stmt = $conn->prepare("SELECT * FROM cocurricular_groups WHERE group_id = ?");
$group_stmt->bind_param("i", $group_id);
$group_stmt->execute();
$group_result = $group_stmt->get_result();
$group = $group_result->fetch_assoc();

// Fetch members
$member_stmt = $conn->prepare("SELECT s.student_ic, s.student_fname FROM student_club_membership scm JOIN student s ON scm.student_ic = s.student_ic WHERE scm.group_id = ?");
$member_stmt->bind_param("i", $group_id);
$member_stmt->execute();
$members = $member_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Roles with their corresponding POST field names
    $roles = [
        'president_ic' => 'president',
        'vice_president_ic' => 'vice_president',
        'secretary_ic' => 'secretary',
        'vice_secretary_ic' => 'vice_secretary',
        'treasurer_ic' => 'treasurer',
        'vice_treasurer_ic' => 'vice_treasurer',
        'exco_y6_ic' => 'exco_y6',
        'exco_y5_ic' => 'exco_y5',
        'exco_y4_ic' => 'exco_y4'
    ];

    // Collect assigned roles from form POST
    $assigned = [];
    foreach ($roles as $field => $role) {
        if (!empty($_POST[$field])) {
            $assigned[$_POST[$field]] = $role;
        }
    }

    // Step 1: Get current role holders for this group
    $current = [];
    $stmt = $conn->prepare("SELECT student_ic, membership_role FROM student_club_membership WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $current[$row['student_ic']] = $row['membership_role'];
    }

    // Step 2: Demote all roles to 'member' (except those already members)
    $stmt = $conn->prepare("UPDATE student_club_membership SET membership_role = 'member' WHERE group_id = ? AND membership_role != 'member'");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();

    // Step 3: Assign new roles to students
    foreach ($assigned as $student_ic => $new_role) {
        // Check if the student is already a member of the group
        $check = $conn->prepare("SELECT * FROM student_club_membership WHERE student_ic = ? AND group_id = ?");
        $check->bind_param("si", $student_ic, $group_id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;

        if ($exists) {
            // Update membership role
            $update = $conn->prepare("UPDATE student_club_membership SET membership_role = ? WHERE student_ic = ? AND group_id = ?");
            $update->bind_param("ssi", $new_role, $student_ic, $group_id);
            $update->execute();
        } else {
            // Insert new member with role
            $insert = $conn->prepare("INSERT INTO student_club_membership (student_ic, group_id, membership_role) VALUES (?, ?, ?)");
            $insert->bind_param("sis", $student_ic, $group_id, $new_role);
            $insert->execute();
        }
    }

    // Step 4: Update cocurricular_groups table with all fields
    $fieldsToUpdate = [
        'group_name',
        'group_type',
        'group_description',
        'advisor_name',
        'advisor_ic'
    ];
    // Add role fields (column names)
    $fieldsToUpdate = array_merge($fieldsToUpdate, array_keys($roles));

    // Build SET part of query dynamically
    $setParts = [];
    foreach ($fieldsToUpdate as $field) {
        $setParts[] = "$field = ?";
    }
    $setQuery = implode(", ", $setParts);

    $sql = "UPDATE cocurricular_groups SET $setQuery WHERE group_id = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameters: all strings except last param (group_id is int)
    $type_string = str_repeat("s", count($fieldsToUpdate)) . "i";

    // Collect values from POST for the fields
    $bind_values = [];
    foreach ($fieldsToUpdate as $field) {
        if (in_array($field, array_keys($roles))) {
            // Role fields, could be empty (null)
            $bind_values[] = !empty($_POST[$field]) ? $_POST[$field] : null;
        } else {
            // Other fields from POST directly
            $bind_values[] = $_POST[$field];
        }
    }
    $bind_values[] = $group_id; // WHERE clause

    // Prepare bind_param arguments by reference
    $params = [];
    $params[] = &$type_string;
    foreach ($bind_values as $key => $value) {
        $params[] = &$bind_values[$key];
    }

    call_user_func_array([$stmt, 'bind_param'], $params);
    $stmt->execute();

    echo "<script>alert('Kemaskini berjaya!'); window.location.href='cocurricular_info.php?group=" . urlencode($group['group_name']) . "';</script>";
    exit();
}

?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Edit Club - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="css/header&bg.css" />
    <link rel="stylesheet" href="css/form.css" />
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
      <span class="material-symbols-outlined icon">notifications</span>
    </div>
  </header>
    <div class="container">
        <div class="group-form-container">
            <h2>EDIT KELAB: <?= htmlspecialchars($group['group_name']) ?></h2>
            <form method="POST" enctype="multipart/form-data" action="edit_club.php?group_id=<?= $group_id ?>">
                <label>Nama Kumpulan</label>
                <input type="text" name="group_name" value="<?= htmlspecialchars($group['group_name']) ?>" required>

                <label style="margin: 20px 0px 10px;">Kategori Kumpulan</label>
                <?php
                $types = ['uniform_bodies' => 'Badan Beruniform', 'sports' => 'Sukan', 'clubs_associations' => 'Kelab', 'others' => 'Lain-lain'];
                foreach ($types as $val => $label) {
                    $checked = ($group['group_type'] === $val) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='group_type' value='$val' onclick='onlyOne(this)' $checked> $label</label><br>";
                }
                ?>

                <label>Maklumat</label>
                <textarea name="group_description" required><?= htmlspecialchars($group['group_description']) ?></textarea>

                <label>Nama Penasihat</label>
                <input type="text" name="advisor_name" value="<?= htmlspecialchars($group['advisor_name']) ?>" required>

                <label>IC Penasihat</label>
                <input type="text" name="advisor_ic" value="<?= htmlspecialchars($group['advisor_ic']) ?>" required>

                <?php
                function renderDropdown($name, $label, $members, $selected)
                {
                    echo "<label>$label</label><select name='$name'>";
                    echo "<option value=''>-- Pilih Pelajar (Kosongkan jika tiada) --</option>";
                    foreach ($members as $student) {
                        $value = htmlspecialchars($student['student_ic']);
                        $full = htmlspecialchars($student['student_fname'] . ' ' . $student['student_lname']);
                        $isSelected = ($selected === $value) ? 'selected' : '';
                        echo "<option value='$value' $isSelected>$full</option>";
                    }
                    echo "</select>";
                }

                renderDropdown('president_ic', 'Presiden', $members, $group['president_ic']);
                renderDropdown('vice_president_ic', 'Naib Presiden', $members, $group['vice_president_ic']);
                renderDropdown('secretary_ic', 'Setiausaha', $members, $group['secretary_ic']);
                renderDropdown('vice_secretary_ic', 'Naib Setiausaha', $members, $group['vice_secretary_ic']);
                renderDropdown('treasurer_ic', 'Bendahari', $members, $group['treasurer_ic']);
                renderDropdown('vice_treasurer_ic', 'Naib Bendahari', $members, $group['vice_treasurer_ic']);
                renderDropdown('exco_y6_ic', 'EXCO Tahun 6', $members, $group['exco_y6_ic']);
                renderDropdown('exco_y5_ic', 'EXCO Tahun 5', $members, $group['exco_y5_ic']);
                renderDropdown('exco_y4_ic', 'EXCO Tahun 4', $members, $group['exco_y4_ic']);
                ?>

                <label>Logo Baru (jika ingin tukar)</label>
                <input type="file" name="logo" accept="image/*">
                <br>
                <div style="text-align:center;">
                    <button type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function onlyOne(checkbox) {
            var checkboxes = document.getElementsByName('group_type');
            checkboxes.forEach((item) => {
                if (item !== checkbox) item.checked = false;
            });
        }
    </script>
</body>

</html>