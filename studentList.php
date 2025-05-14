<?php
require_once 'connect.php';
session_start();

if (!isset($_SESSION['user_ic'])) {
    die("Access denied.");
}

$teacher_ic = $_SESSION['user_ic'];

$sql = "SELECT * FROM teacher INNER JOIN class ON class.class_id = teacher.class WHERE teacher_ic = '$teacher_ic'";
$result = mysqli_query($conn, $sql);
$teacher = mysqli_fetch_assoc($result);

$class_id = $teacher['class'];

$sql_students = "SELECT student.*, class.class_name FROM student INNER JOIN class ON student.student_class = class.class_id WHERE student.student_class = '$class_id' ";
$result_students = mysqli_query($conn, $sql_students);

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

    <header>
        <div class="logo-section">
            <img src="../img/logo.png" alt="Logo" />
            <div class="logo-text">
                <span>SRIAAWP ActivHub</span>
                <div class="nav-links">
                    <a href="../teacher/teacher_dashboard.php">Papan Pemuka</a>
                    <a href="../teacher/teacher_profile.php">Profil</a>
                </div>
            </div>
        </div>

        <div class="icon-section">
            <div class="admin-section">
                <span class="admin-text">Cikgu</span>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>
    <div class="container">
        <div class="teacher-list-container">
            <div class="teacher-list-box">
                <div class="title-bar">
                    <h2>Senarai Murid</h2>
                    <div class="button-group">
                        <button class="btn-red" onclick="location.href='teacher/teacher_dashboard.php'">Batal</button>
                    </div>
                </div>
                <?php
                if (mysqli_num_rows($result_students) > 0) {
                    while ($row = mysqli_fetch_assoc($result_students)) { ?>
                        <div class="teacher-card" id="<?php echo $row["student_ic"]; ?>">
                            <p><strong><?php echo $row["student_fname"]; ?></strong><br>
                                <span class="credentials">Nombor IC :</span> <?php echo $row["student_ic"]; ?><br>
                                <button class="edit-button" onclick="edit(<?php echo $row['student_ic']; ?>)">Edit</button>
                        </div>
                    <?php }
                } else {
                    ?>
                    <div class="teacher-card">
                        <p><strong>Tiada Rekod</strong><br>
                    </div>
                <?php
                }
                ?>

            </div>
        </div>
    </div>
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
            const address = document.getElementsByName("student_address_" + id)[0].value;
            const emergency = document.getElementsByName("student_emergency_" + id)[0].value;
            const guardian_ic = document.getElementsByName("guardian_ic_" + id)[0].value;
            const guardian_name = document.getElementsByName("guardian_name_" + id)[0].value;
            const relationship = document.getElementsByName("relationship_" + id)[0].value;
            const guardian_address = document.getElementsByName("guardian_address_" + id)[0].value;
            const contact_num = document.getElementsByName("contact_num_" + id)[0].value;

            const data = {
                id: id,
                name: name,
                password: password,
                dob: dob,
                doe: doe,
                address: address,
                emergency: emergency,
                guardian_ic: guardian_ic,
                guardian_name: guardian_name,
                relationship: relationship,
                guardian_address: guardian_address,
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
                        alert("Updated Successfully!");
                    } else {
                        alert("Updated Unsuccessfully!");
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