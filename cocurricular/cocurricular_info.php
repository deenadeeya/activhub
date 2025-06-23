<?php
require_once '../includes/session_check.php';
include '../config/connect.php';
include '../includes/header.php';

// Notification count logic for all user types
$pending_count = 0;

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'student') {
        $student_ic = $_SESSION['user_ic'] ?? null;
        if ($student_ic) {
            $query = "
              SELECT COUNT(*) AS pending_count 
              FROM cocu_activities 
              WHERE student_ic = ? 
                AND approval_status IN ('pending', 'approved', 'rejected')
                AND notification_read = 0
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $student_ic);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row_pending = $result->fetch_assoc()) {
                $pending_count = $row_pending['pending_count'];
            }
            $stmt->close();
        }
    } elseif ($_SESSION['user_role'] === 'teacher') {
        $teacher_ic = $_SESSION['user_ic'];
        $sql_class_id = "SELECT class_id FROM class WHERE head_teacher = '$teacher_ic'";
        $result_class_id = mysqli_query($conn, $sql_class_id);
        if ($result_class_id && mysqli_num_rows($result_class_id) > 0) {
            $row_class_id = mysqli_fetch_assoc($result_class_id);
            $teacher_class_id = $row_class_id['class_id'];
            
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
    } elseif ($_SESSION['user_role'] === 'admin') {
        // Admin notification count - all pending approvals
        $pending_query = "SELECT COUNT(*) AS total_pending FROM cocu_activities WHERE approval_status = 'pending'";
        $pending_result = mysqli_query($conn, $pending_query);
        if ($pending_result) {
            $pending_data = mysqli_fetch_assoc($pending_result);
            $pending_count = $pending_data['total_pending'];
        }
    }
}

if (!isset($_GET['group'])) {
    echo "No group selected.";
    exit();
}

$groupName = urldecode($_GET['group']);

// Fetch group info first to get the group ID
$sql = "SELECT * FROM cocurricular_groups WHERE group_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $groupName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Group not found.";
    exit();
}

$group = $result->fetch_assoc();
$groupId = $group['group_id'];

// === HANDLE MEMBER REMOVAL POST ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Remove member logic
    if (isset($_POST['remove_member_ic'], $_POST['remove_member_group'])) {
        // Only allow if admin or teacher
        if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')) {
            $removeIc = $_POST['remove_member_ic'];
            $removeGroupId = (int)$_POST['remove_member_group'];

            if ($removeGroupId === $groupId) {
                $deleteStmt = $conn->prepare("DELETE FROM student_club_membership WHERE student_ic = ? AND group_id = ?");
                $deleteStmt->bind_param("si", $removeIc, $removeGroupId);
                if ($deleteStmt->execute()) {
                    // Redirect to refresh the page after deletion
                    header("Location: cocurricular_info.php?group=" . urlencode($groupName));
                    exit();
                } else {
                    echo "<script>alert('Failed to remove member.');</script>";
                }
            }
        }
    }

    // Add member logic
    if (isset($_POST['add_member'])) {
        $student_ics = $_POST['student_ic']; // Now an array
        $role = 'member';
        $success_count = 0;
        $error_count = 0;
        $duplicate_count = 0;
        $added_students = [];

        if (!is_array($student_ics)) {
            $student_ics = [$student_ics];
        }

        foreach ($student_ics as $student_ic) {
            $checkStmt = $conn->prepare("SELECT * FROM student_club_membership WHERE student_ic = ? AND group_id = ?");
            $checkStmt->bind_param("si", $student_ic, $groupId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows === 0) {
                $insertStmt = $conn->prepare("INSERT INTO student_club_membership (student_ic, group_id, membership_role) VALUES (?, ?, ?)");
                $insertStmt->bind_param("sis", $student_ic, $groupId, $role);
                if ($insertStmt->execute()) {
                    // Get student name for success message
                    $nameStmt = $conn->prepare("SELECT student_fname FROM student WHERE student_ic = ?");
                    $nameStmt->bind_param("s", $student_ic);
                    $nameStmt->execute();
                    $nameResult = $nameStmt->get_result();
                    $studentName = $nameResult->fetch_assoc()['student_fname'];
                    
                    $added_students[] = $studentName;
                    $success_count++;
                } else {
                    $error_count++;
                }
            } else {
                $duplicate_count++;
            }
        }

        // Generate appropriate success/error messages
        if ($success_count > 0) {
            if ($success_count == 1) {
                $success_message = "Berjaya menambah " . htmlspecialchars($added_students[0]) . " ke dalam kumpulan!";
            } else {
                $success_message = "Berjaya menambah " . $success_count . " pelajar ke dalam kumpulan: " . htmlspecialchars(implode(', ', $added_students));
            }
        }

        if ($duplicate_count > 0 || $error_count > 0) {
            $error_parts = [];
            if ($duplicate_count > 0) {
                $error_parts[] = $duplicate_count . " pelajar sudah menjadi ahli kumpulan";
            }
            if ($error_count > 0) {
                $error_parts[] = $error_count . " pelajar gagal ditambah";
            }
            $error_message = implode(', ', $error_parts) . ".";
        }
        
        // Don't redirect, so messages can be shown
    }

    // Delete group logic
    if (isset($_POST['delete'])) {
        if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')) {
            $logoPath = $group['logo_path'];
            $delStmt = $conn->prepare("DELETE FROM cocurricular_groups WHERE group_id = ?");
            $delStmt->bind_param("i", $groupId);

            if ($delStmt->execute()) {
                if (!empty($logoPath) && file_exists($logoPath)) {
                    unlink($logoPath);
                }
                echo "<script>alert('Group deleted successfully.'); window.location.href='cocurricular_board.php';</script>";
                exit();
            } else {
                echo "Error deleting group.";
                exit();
            }
        }
    }
}

// Count members
$countStmt = $conn->prepare("SELECT COUNT(*) AS member_count FROM student_club_membership WHERE group_id = ?");
$countStmt->bind_param("i", $groupId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalMembers = $countResult->fetch_assoc()['member_count'];

// Fetch group events
$activityQuery = $conn->prepare("SELECT event_name, event_start_date, event_end_date, event_venue FROM events WHERE group_id = ?");
$activityQuery->bind_param("i", $groupId);
$activityQuery->execute();
$activityResult = $activityQuery->get_result();

// Fetch group members with student_ic (needed for removal form)
$memberQuery = $conn->prepare("SELECT s.student_ic, s.student_fname, scm.membership_role FROM student_club_membership scm JOIN student s ON scm.student_ic = s.student_ic WHERE scm.group_id = ?");
$memberQuery->bind_param("i", $groupId);
$memberQuery->execute();
$memberResult = $memberQuery->get_result();

// Fetch students not yet members with class information
$nonMemberQuery = $conn->prepare("
    SELECT s.student_ic, s.student_fname, c.class_name
    FROM student s
    JOIN class c ON s.student_class = c.class_id
    WHERE s.student_ic NOT IN (
        SELECT student_ic FROM student_club_membership WHERE group_id = ?
    )
    ORDER BY s.student_fname
");
$nonMemberQuery->bind_param("i", $groupId);
$nonMemberQuery->execute();
$nonMemberResult = $nonMemberQuery->get_result();
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Kokurikulum - SRIAAWP ActivHub</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/header&bg.css">
    <link rel="stylesheet" href="../assets/css/cocu_board_info.css">
    <link rel="stylesheet" href="../assets/css/button.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon">
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
                    } elseif ($_SESSION['user_role'] === 'teacher' && isset($teacher) && !empty($teacher['teacher_fname'])) {
                        echo '<span class="admin-text">' . strtoupper($teacher['teacher_fname']) . '</span><br>';
                    } elseif ($_SESSION['user_role'] === 'student' && isset($student) && !empty($student['student_fname'])) {
                        echo '<span class="admin-text">' . strtoupper($student['student_fname']) . '</span><br>';
                    } else {
                        // Fallback display for user role
                        echo '<span class="admin-text">' . strtoupper($_SESSION['user_role']) . '</span><br>';
                    }
                }
                ?>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            
            <?php include '../includes/notifications_panel.php'; ?>
        </div>
    </header>

    <div class="container">
        <h2>PAPAN KOKURIKULUM</h2>
        <div class="info-container">
            <div class="header">
                <div class="spacer"></div>
                <a class="return-button" href="cocurricular_board.php">KEMBALI</a>
            </div>

            <div class="section-title">MAKLUMAT</div>
            <div class="content">
                <?php
                $logoPath = $group['logo_path'];
                // Fix logo path if it's using old structure
                if (!empty($logoPath) && !str_starts_with($logoPath, '../assets/')) {
                    // If path starts with 'logos/', convert to assets path
                    if (str_starts_with($logoPath, 'logos/')) {
                        $logoPath = '../assets/' . $logoPath;
                    }
                    // If path starts with '../logos/', convert to assets path  
                    elseif (str_starts_with($logoPath, '../logos/')) {
                        $logoPath = str_replace('../logos/', '../assets/logos/', $logoPath);
                    }
                }
                ?>
                <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo" class="group-logo">
                <div>
                    <h3><?= htmlspecialchars($group['group_name']) ?></h3>
                    <p><strong>Penasihat:</strong> <?= htmlspecialchars($group['advisor_name']) ?></p>
                    <p><strong>Jumlah Ahli:</strong> <?= $totalMembers ?> murid-murid</p>
                </div>
            </div>

            <div class="description">
                <div class="section-title">VISI & MISI</div>
                <p><?= nl2br(htmlspecialchars($group['group_description'])) ?></p>
            </div>

            <div class="section-title">AKTIVITI</div>
            <ul class="activities">
                <?php
                $activityCount = 0;
                while ($activity = $activityResult->fetch_assoc()):
                    $activityCount++;
                ?>
                    <li>
                        <strong><?= $activityCount ?>. <?= htmlspecialchars($activity['event_name']) ?></strong><br>
                        <?= date('d/m/Y', strtotime($activity['event_start_date'])) ?> to <?= date('d/m/Y', strtotime($activity['event_end_date'])) ?><br>
                        Venue: <?= htmlspecialchars($activity['event_venue']) ?>
                    </li>
                <?php endwhile; ?>
                <?php if ($activityCount === 0): ?>
                    <li>Tiada aktiviti diluluskan setakat ini.</li>
                <?php endif; ?>
            </ul>

            <div class="section-title">AHLI KUMPULAN</div>
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
            <table class="member-table">
                <thead>
                    <tr>
                        <th>Nama Pelajar</th>
                        <th>Jawatan</th>
                        <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')): ?>
                            <th>Tindakan</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($member = $memberResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($member['student_fname']) ?></td>
                            <td><?= htmlspecialchars($role_labels[$member['membership_role']] ?? 'Ahli') ?></td>
                            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'teacher')): ?>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Remove this member?');" style="margin:0;">
                                        <input type="hidden" name="remove_member_ic" value="<?= htmlspecialchars($member['student_ic']) ?>">
                                        <input type="hidden" name="remove_member_group" value="<?= $groupId ?>">
                                        <button type="submit" class="delete-button">Remove</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>

            <?php if ((isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') || $_SESSION['user_role'] === 'teacher'): ?>
                <div class="section-title">TAMBAH AHLI BIASA</div>
                
                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="success-message">
                        <span class="message-icon">✅</span><?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <span class="message-icon">❌</span><?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="add-member-section">
                    <form method="POST" id="addMemberForm">
                        <div class="search-container">
                            <input type="text" id="studentSearch" placeholder="Taip nama pelajar untuk mencari..." autocomplete="off">
                            <div id="searchResults" class="search-results"></div>
                            <div id="selectedStudentsList" class="selected-students-list"></div>
                        </div>
                        <button type="submit" name="add_member" class="edit-button" id="addButton" disabled>Tambah Ahli</button>
                        <button type="button" id="clearAllButton" class="delete-button" style="margin-left: 10px; display: none;">Buang Semua</button>
                    </form>
                </div>

                <style>
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
                    text-align: center;
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
                    text-align: center;
                }

                .message-icon {
                    margin-right: 8px;
                    font-weight: bold;
                }

                .add-member-section {
                    text-align: center;
                    margin: 20px 0;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 8px;
                    border: 2px solid #e9ecef;
                }

                .search-container {
                    position: relative;
                    display: inline-block;
                    width: 100%;
                    max-width: 400px;
                    margin-bottom: 15px;
                }

                #studentSearch {
                    width: 100%;
                    padding: 12px 16px;
                    font-size: 16px;
                    border: 2px solid #ced4da;
                    border-radius: 8px;
                    box-sizing: border-box;
                    transition: border-color 0.3s;
                }

                #studentSearch:focus {
                    outline: none;
                    border-color: #064789;
                    box-shadow: 0 0 0 3px rgba(6, 71, 137, 0.1);
                }

                .search-results {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    border: 2px solid #ced4da;
                    border-top: none;
                    border-radius: 0 0 8px 8px;
                    max-height: 250px;
                    overflow-y: auto;
                    z-index: 1000;
                    display: none;
                }

                .search-result-item {
                    padding: 12px 16px;
                    cursor: pointer;
                    border-bottom: 1px solid #e9ecef;
                    transition: background-color 0.2s;
                    text-align: left;
                }

                .search-result-item:hover {
                    background-color: #f8f9fa;
                }

                .search-result-item:last-child {
                    border-bottom: none;
                }

                .student-name {
                    font-weight: bold;
                    color: #064789;
                    margin-bottom: 2px;
                }

                .student-details {
                    font-size: 0.85em;
                    color: #666;
                }

                .selected-students-list {
                    margin-top: 15px;
                    max-height: 200px;
                    overflow-y: auto;
                    border: 2px solid #e9ecef;
                    border-radius: 8px;
                    background: white;
                    display: none;
                }

                .selected-student-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 15px;
                    border-bottom: 1px solid #e9ecef;
                    transition: background-color 0.2s;
                }

                .selected-student-item:hover {
                    background-color: #f8f9fa;
                }

                .selected-student-item:last-child {
                    border-bottom: none;
                }

                .selected-student-info {
                    flex: 1;
                    text-align: left;
                }

                .selected-student-name {
                    font-weight: bold;
                    color: #064789;
                    margin-bottom: 2px;
                }

                .selected-student-details {
                    font-size: 0.85em;
                    color: #666;
                }

                .remove-student-btn {
                    background: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    padding: 4px 8px;
                    cursor: pointer;
                    font-size: 0.8em;
                    transition: background-color 0.2s;
                }

                .remove-student-btn:hover {
                    background: #c82333;
                }

                #clearAllButton {
                    background-color: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    padding: 8px 16px;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }

                #clearAllButton:hover {
                    background-color: #c82333;
                }

                .selected-student {
                    margin-top: 10px;
                    padding: 8px 12px;
                    background: #e7f3ff;
                    border: 2px solid #064789;
                    border-radius: 6px;
                    color: #064789;
                    font-weight: bold;
                    display: none;
                }

                #addButton:disabled {
                    background-color: #6c757d;
                    cursor: not-allowed;
                    opacity: 0.6;
                }

                .no-results {
                    padding: 12px 16px;
                    color: #6c757d;
                    font-style: italic;
                }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('studentSearch');
                    const searchResults = document.getElementById('searchResults');
                    const selectedStudentsList = document.getElementById('selectedStudentsList');
                    const addButton = document.getElementById('addButton');
                    const clearAllButton = document.getElementById('clearAllButton');
                    
                    let selectedStudents = [];
                    
                    // Students data (from PHP) - now includes class info
                    const students = [
                        <?php 
                        // Reset the result pointer to fetch students again
                        $nonMemberResult->data_seek(0);
                        $studentArray = [];
                        while ($row = $nonMemberResult->fetch_assoc()) {
                            $studentArray[] = "{ic: '" . addslashes($row['student_ic']) . "', name: '" . addslashes($row['student_fname']) . "', class: '" . addslashes($row['class_name']) . "'}";
                        }
                        echo implode(', ', $studentArray);
                        ?>
                    ];

                    searchInput.addEventListener('input', function() {
                        const query = this.value.toLowerCase().trim();
                        
                        if (query.length < 2) {
                            searchResults.style.display = 'none';
                            return;
                        }

                        const filteredStudents = students.filter(student => 
                            !selectedStudents.some(selected => selected.ic === student.ic) &&
                            (student.name.toLowerCase().includes(query) || 
                            student.class.toLowerCase().includes(query) ||
                            student.ic.includes(query))
                        );

                        displayResults(filteredStudents);
                    });

                    function displayResults(students) {
                        searchResults.innerHTML = '';
                        
                        if (students.length === 0) {
                            searchResults.innerHTML = '<div class="no-results">Tiada pelajar ditemui</div>';
                        } else {
                            students.slice(0, 10).forEach(student => { // Limit to 10 results
                                const item = document.createElement('div');
                                item.className = 'search-result-item';
                                item.innerHTML = `
                                    <div class="student-name">${student.name}</div>
                                    <div class="student-details">Kelas: ${student.class} | IC: ${student.ic}</div>
                                `;
                                item.addEventListener('click', function() {
                                    selectStudent(student);
                                });
                                searchResults.appendChild(item);
                            });
                        }
                        
                        searchResults.style.display = 'block';
                    }

                    function selectStudent(student) {
                        // Check if student already selected
                        if (selectedStudents.some(selected => selected.ic === student.ic)) {
                            return;
                        }

                        selectedStudents.push(student);
                        updateSelectedStudentsList();
                        searchInput.value = '';
                        searchResults.style.display = 'none';
                        updateButtons();
                    }

                    function updateSelectedStudentsList() {
                        if (selectedStudents.length === 0) {
                            selectedStudentsList.style.display = 'none';
                            return;
                        }

                        selectedStudentsList.style.display = 'block';
                        selectedStudentsList.innerHTML = selectedStudents.map((student, index) => `
                            <div class="selected-student-item">
                                <div class="selected-student-info">
                                    <div class="selected-student-name">${student.name}</div>
                                    <div class="selected-student-details">Kelas: ${student.class} | IC: ${student.ic}</div>
                                </div>
                                <button type="button" class="remove-student-btn" onclick="removeStudent(${index})">Buang</button>
                            </div>
                        `).join('');

                        // Add hidden inputs for form submission
                        const existingInputs = document.querySelectorAll('input[name="student_ic[]"]');
                        existingInputs.forEach(input => input.remove());

                        selectedStudents.forEach(student => {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'student_ic[]';
                            hiddenInput.value = student.ic;
                            document.getElementById('addMemberForm').appendChild(hiddenInput);
                        });
                    }

                    window.removeStudent = function(index) {
                        selectedStudents.splice(index, 1);
                        updateSelectedStudentsList();
                        updateButtons();
                    }

                    function updateButtons() {
                        if (selectedStudents.length > 0) {
                            addButton.disabled = false;
                            addButton.textContent = `Tambah ${selectedStudents.length} Ahli`;
                            clearAllButton.style.display = 'inline-block';
                        } else {
                            addButton.disabled = true;
                            addButton.textContent = 'Tambah Ahli';
                            clearAllButton.style.display = 'none';
                        }
                    }

                    clearAllButton.addEventListener('click', function() {
                        selectedStudents = [];
                        updateSelectedStudentsList();
                        updateButtons();
                    });

                    // Hide results when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                            searchResults.style.display = 'none';
                        }
                    });

                    // Auto-hide success/error messages after 5 seconds
                    setTimeout(function() {
                        const successMsg = document.querySelector('.success-message');
                        const errorMsg = document.querySelector('.error-message');
                        if (successMsg) {
                            successMsg.style.transition = 'opacity 0.5s';
                            successMsg.style.opacity = '0';
                            setTimeout(() => successMsg.remove(), 500);
                        }
                        if (errorMsg) {
                            errorMsg.style.transition = 'opacity 0.5s';
                            errorMsg.style.opacity = '0';
                            setTimeout(() => errorMsg.remove(), 500);
                        }
                    }, 5000);
                });
                </script>
                <div class="button-group">
                    <a href="edit_club.php?group_id=<?= $group['group_id'] ?>" class="edit-button">Edit Kumpulan</a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this group?');" style="display:inline;">
                        <button type="submit" name="delete" class="delete-button">Buang Kumpulan</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>