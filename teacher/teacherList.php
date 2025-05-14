<?php
require_once '..\connect.php';

$sql = "SELECT * FROM teacher INNER JOIN class ON class.class_id = teacher.class";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Teacher List - SRI AL-AMIN ActivHub</title>
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
                <span>SRI AL-AMIN ActivHub</span>
                <div class="nav-links">
                    <a href="../admin/admin_dashboard.php">Papan Pemuka</a>
                    <a href="../admin/admin_profile.php">Profil</a>
                </div>
            </div>
        </div>

        <div class="icon-section">
            <div class="admin-section">
                <span class="admin-text">Admin</span>
                <span class="welcome-text">Selamat Kembali!</span>
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
                        <button class=" btn-red" onclick="location.href='../admin/admin_dashboard.php'">Batal</button>
                    </div>
                </div>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { ?>
                        <div class="teacher-card" id="<?php echo $row["teacher_ic"] ?>">
                            <p><strong> <?php echo $row["teacher_fname"] ?> </strong><br>
                                <strong>Class:</strong> <?php echo $row["class_name"] ?><br>
                                <span class="credentials">Nombor IC :</span> <?php echo $row["teacher_ic"] ?><br>
                                <button class="edit-button" onclick="edit(<?php echo $row['teacher_ic'] ?>)">Edit</button>
                        </div>
                    <?php }
                } else { ?>
                    <div class="teacher-card">
                        <p><strong>Tiada Rekod</strong><br>
                    </div>
                <?php
                }

                ?>

            </div>
        </div>
    </div>
</body>

</html>

<script>
    function edit(id) {
        const data = {
            id: id
        };

        fetch('function/get_teacher.php', {
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

    function cancel(id) {
        const data = {
            id: id
        };

        fetch('function/teacher_list.php', {
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
        var name = document.getElementsByName("edit_name_" + id)[0].value;
        var password = document.getElementsByName("edit_password_" + id)[0].value;
        var class1 = document.getElementsByName("class_" + id)[0].value;

        if (name == "" || class1 == "" || id == "") {
            alert("Please fill all field!");
        } else {
            const data = {
                id: id,
                name: name,
                password: password,
                class1: class1
            };

            fetch('function/teacher_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status == 1) {
                        alert("Updated " + id + " successfully!");
                        document.getElementById(id).innerHTML = result.message;
                    } else {
                        alert("Updated " + id + " unsuccessfully!");
                        document.getElementById(id).innerHTML = result.message;
                    }

                })
                .catch(error => {
                    console.error('Error:', error);
                });

        }

    }
</script>