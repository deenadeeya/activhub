<?php
session_start();
require_once '../connect.php';
include '../header.php';
// Auto logout after 30 minutes of inactivity
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?expired=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time

// Check if user is logged in and is admin
if (!isset($_SESSION['user_ic']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$sql = "SELECT * FROM class";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Senarai Admin - SRIAAWP ActivHub</title>
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
                    <h2>Senarai Kelas</h2>
                    <div class="button-group">
                        <button class="btn-yellow" onclick="location.href='admin_add_class.php'">Tambah Kelas Baru</button>
                        <button class="btn-red" onclick="location.href='admin_dashboard.php'">Batal</button>
                    </div>
                </div>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { ?>
                        <div class="teacher-card" id="<?php echo $row["class_id"] ?>">
                            <p>
                                <strong>Nama Kelas:</strong> <?php echo $row["class_name"] ?><br>
                                <br>
                                <button class="edit-button" onclick="edit('<?php echo $row['class_id'] ?>')">Kemas Kini</button>
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

        fetch('function/get_class.php', {
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

        fetch('../admin/function/class_list.php', {
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

    function delete_(id) {
        const data = {
            id: id
        };

        fetch('function/class_delete.php', {
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
                location.href = "admin_classList.php";
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function save(id) {
        var class_year = document.getElementsByName("edit_year_" + id)[0].value;
        var class_name = document.getElementsByName("edit_name_" + id)[0].value;
        var head_teacher = document.getElementsByName("teacher_" + id)[0].value;
        if (class_year == "" || class_name == "" || head_teacher == "") {
            alert("Please fill all field!");
        } else {
            const data = {
                id: id,
                class_year: class_year,
                class_name: class_name,
                head_teacher: head_teacher
            };

            fetch('function/class_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status == 1) {
                        alert("Kemas Kini " + class_name + " Berjaya!");
                        document.getElementById(id).innerHTML = result.message;
                    } else {
                        alert("Kemas Kini " + class_name + " Tidak Berjaya!");
                        document.getElementById(id).innerHTML = result.message;
                    }

                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }
</script>