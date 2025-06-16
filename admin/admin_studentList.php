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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                    <form action="function/import_excel.php" method="post" enctype="multipart/form-data">
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required>
                        <button type="submit" class="btn-yellow">Import Excel</button>
                    </form>
                    <br><br>
                    <a href="function/generate_template.php" class="btn-blue">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="teacher-card" id="<?= $row['student_ic'] ?>">
                            <p><strong><?= $row['student_fname'] ?></strong><br>
                                <strong>Kelas:</strong> <?= $row['class_name'] ?><br>
                                <span class="credentials">Nombor Kad Pengenalan:</span> <?= $row['student_ic'] ?><br>
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
    <div id="importResultModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Import Results</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="result-grid">
                    <div class="result-item success">
                        <div class="result-icon">✅</div>
                        <div class="result-text">
                            <span id="success-count">0</span> students added
                        </div>
                    </div>
                    <div class="result-item warning">
                        <div class="result-icon">⚠️</div>
                        <div class="result-text">
                            <span id="invalid-count">0</span> invalid rows
                        </div>
                    </div>
                    <div class="result-item info">
                        <div class="result-icon">🔄</div>
                        <div class="result-text">
                            <span id="duplicate-count">0</span> duplicates skipped
                        </div>
                    </div>
                    <div class="result-item error">
                        <div class="result-icon">❌</div>
                        <div class="result-text">
                            <span id="fail-count">0</span> failed inserts
                        </div>
                    </div>
                </div>
                <div id="error-details" class="error-details"></div>
            </div>
            <div class="modal-footer">
                <button class="modal-close-btn">OK</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['import_result'])) : ?>
                const result = <?php echo json_encode($_SESSION['import_result']); ?>;
                const modal = document.getElementById('importResultModal');

                // Ensure modal exists
                if (modal) {
                    // Populate data
                    if (result.success !== undefined) document.getElementById('success-count').textContent = result.success;
                    if (result.invalid !== undefined) document.getElementById('invalid-count').textContent = result.invalid;
                    if (result.duplicate !== undefined) document.getElementById('duplicate-count').textContent = result.duplicate;
                    if (result.fail !== undefined) document.getElementById('fail-count').textContent = result.fail;

                    // Show error details if any
                    if (result.errors && result.errors.length > 0) {
                        const errorContainer = document.getElementById('error-details');
                        if (errorContainer) {
                            errorContainer.innerHTML = '<h4>Error Details:</h4><ul>' +
                                result.errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
                        }
                    }

                    // Show modal
                    modal.style.display = 'block';

                    // Close handlers
                    const closeModal = () => modal.style.display = 'none';

                    document.querySelector('.close-modal')?.addEventListener('click', closeModal);
                    document.querySelector('.modal-close-btn')?.addEventListener('click', closeModal);

                    window.addEventListener('click', function(event) {
                        if (event.target == modal) {
                            closeModal();
                        }
                    });

                    // Clear session data
                    fetch('clear_import_result.php', {
                            method: 'POST'
                        })
                        .catch(err => console.error('Error clearing session:', err));
                }
            <?php
                unset($_SESSION['import_result']);
            endif;
            ?>
        });
    </script>
</body>

</html>