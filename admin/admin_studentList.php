<?php
session_start();
require_once '../connect.php';
include '../header.php';

$sql = "SELECT * FROM student INNER JOIN class ON class.class_id = student.student_class";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Senarai Pelajar - SRIAAWP ActivHub</title>
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
                <?php
                if (isset($_SESSION['user_role'])) {
                    if ($_SESSION['user_role'] === 'admin') {
                        echo '<span class="admin-text">' . strtoupper($_SESSION['admin_name'] ?? 'ADMIN') . '</span><br>';
                    } 
                }
                ?>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <div class="teacher-list-container">
            <div class="teacher-list-box">
                <div class="title-bar">
                    <h2>SENARAI MURID</h2>
                    <div class="button-group">
                        <button class="btn-yellow" onclick="window.location.href='admin_add_student.php'">Tambah Pelajar Baru</button>
                        <button class="btn-red" onclick="location.href='admin_dashboard.php'">Batal</button>
                    </div>
                </div>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="teacher-card" id="<?= $row['student_ic'] ?>">
                            <p><strong><?= $row['student_fname'] ?></strong><br>
                                <strong>Class:</strong> <?= $row['class_name'] ?><br>
                                <span class="credentials">Nombor IC:</span> <?= $row['student_ic'] ?><br>
                                <button class="edit-button" onclick="edit(<?= $row['student_ic'] ?>)">Edit</button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="teacher-card">
                        <p><strong>Tiada Rekod</strong><br>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function edit(id) {
            fetch('../admin/function/get_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById(id).innerHTML = result.message;
                });
        }

        function cancel(id) {
            fetch('../admin/function/student_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById(id).innerHTML = result.message;
                });
        }

        function save(id) {
            var name = document.getElementsByName("edit_name_" + id)[0].value;
            var password = document.getElementsByName("edit_password_" + id)[0].value;
            var class1 = document.getElementsByName("class_" + id)[0].value;

            if (name == "" || class1 == "" || id == "") {
                alert("Please fill all fields!");
                return;
            }

            const data = {
                id,
                name,
                password,
                class1
            };

            fetch('../admin/function/student_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    alert(result.status == 1 ? "Updated successfully!" : "Update failed!");
                    document.getElementById(id).innerHTML = result.message;
                });
        }
    </script>
</body>

</html>