<?php
session_start();
require_once '../connect.php';
include '../header.php';

// Get all teachers and their class assignments (if any)
$sql = "SELECT t.*, c.class_id, c.class_name 
        FROM teacher t 
        LEFT JOIN class c ON t.teacher_ic = c.head_teacher
        ORDER BY t.teacher_fname";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Senarai Guru - SRI AL-AMIN ActivHub</title>
    <link rel="stylesheet" href="../css/teacherList.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
</head>

<body>
    <header>
        <div class="logo-section">
            <img src="../img/logo.png" alt="Logo" />
            <div class="logo-text">
                <span>SRIAAWP ActivHub</span>
                <?php include '../navlinks.php'; ?>
            </div>
        </div>
        <div class="icon-section">
            <div class="user-section">
                <span class="welcome-text">Selamat Kembali!<br> <?= htmlspecialchars($_SESSION['user_ic']) ?></span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <div class="teacher-list-container">
            <div class="teacher-list-box">
                <div class="title-bar">
                    <h2>Senarai Guru</h2>
                    <div class="button-group">
                        <button class="btn-yellow" onclick="location.href='../admin/admin_add_teacher.php'">Tambah Guru Baru</button>
                        <button class="btn-red" onclick="location.href='../admin/admin_dashboard.php'">Batal</button>
                    </div>
                </div>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="teacher-card" id="teacher-<?php echo $row["teacher_ic"]; ?>">
                            <p>
                                <strong><?php echo htmlspecialchars($row["teacher_fname"]); ?></strong><br>
                                <?php if (!empty($row["class_name"])): ?>
                                    <strong>Kelas:</strong> <?php echo htmlspecialchars($row["class_name"]); ?><br>
                                <?php else: ?>
                                    <strong>Kelas:</strong> Tiada Penugasan<br>
                                <?php endif; ?>
                                <span class="credentials">No. Kad Pengenalan:</span> <?php echo htmlspecialchars($row["teacher_ic"]); ?><br>
                                <button class="edit-button" onclick="editTeacher('<?php echo $row["teacher_ic"]; ?>')">Kemas Kini</button>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="teacher-card">
                        <p><strong>Tiada Rekod Guru</strong></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function editTeacher(id) {
            fetch('function/get_teacher.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Rangkaian tidak berfungsi');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 1) {
                        document.getElementById(`teacher-${id}`).innerHTML = data.message;
                    } else {
                        throw new Error(data.error || 'Ralat tidak diketahui');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ralat: ' + error.message);
                });
        }

        function delete_(id) {
            const data = {
                id: id
            };

            fetch('function/teacher_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status == 1) {
                        alert("Delete " + id + " successfully!");
                    } else {
                        alert("Delete " + id + " unsuccessfully!");
                    }
                    location.href = "teacherList.php";
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function saveTeacher(id) {
            const formData = {
                id: id,
                name: document.querySelector(`input[name="edit_name_${id}"]`).value,
                uname: document.querySelector(`input[name="edit_uname_${id}"]`).value,
                password: document.querySelector(`input[name="edit_password_${id}"]`).value,
                class: document.querySelector(`select[name="class_${id}"]`).value
            };

            if (!formData.name || !formData.uname) {
                alert('Sila isi nama dan username!');
                return;
            }

            fetch('function/teacher_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    if (!response.ok) throw new Error('Rangkaian tidak berfungsi');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 1) {
                        alert('Maklumat guru berjaya dikemaskini!');
                        location.reload();
                    } else {
                        throw new Error(data.error || 'Kemaskini gagal');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ralat: ' + error.message);
                });
        }
    </script>
</body>

</html>