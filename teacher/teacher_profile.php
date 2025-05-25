<?php
session_start();
include '../connect.php';
include '../header.php';


if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    echo "Unauthorized access. Please <a href='../login.php'>login again</a>.";
    exit;
}

$teacher_ic = $_SESSION['user_ic'];

$sql = "SELECT * FROM teacher WHERE teacher_ic = '$teacher_ic'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  $teacher = mysqli_fetch_assoc($result);
}

$query = "SELECT t.*, c.class_name FROM teacher t INNER JOIN class c ON t.class = c.class_id WHERE t.teacher_ic = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $teacher_ic);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "No teacher data found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil Guru - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../css/profile.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
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
            <div class="admin-section">
                <span class="admin-text"><?php echo strtoupper($teacher['teacher_fname']); ?></span><br>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <h1 class="profile-title">PROFIL GURU</h1>

        <main class="profile-container">
            <form action="function/teacher_update_info.php" method="POST">
                <section class="left-card">
                    <div class="profile-header">
                        <img src="../img/profile.jpg" alt="Profile Image" class="profile-pic">
                        <h2>Selamat Datang,<br><span><?php echo strtoupper($row['teacher_fname']); ?></span></h2>
                    </div>

                    <div class="info-group">

                        <label>NAMA PENUH:</label>
                        <input type="text" name="teacher_fname" value=" <?php echo strtoupper($row['teacher_fname']); ?>" required>

                        <label>NOMBOR IC:</label>
                        <input type="text" name="teacher_ic" value=" <?php echo $row['teacher_ic']; ?>" readonly>

                        <label>NOMBOR TELEFON:</label>
                        <input type="text" name="teacher_contact" value=" <?php echo $row['teacher_contact']; ?>" required>


                        <label>EMEL:</label>
                        <input type="email" name="teacher_email" value="<?php echo $row['teacher_email']; ?>" required>

                        <label>TARIKH:</label>
                        <input type="date" name="teacher_dob" value="<?php echo $row['teacher_dob']; ?>" required>

                        <label>TARIKH MASUK KERJA:</label>
                        <input type="date" name="teacher_doe" value="<?php echo $row['teacher_doe']; ?>" required>

                        <label>ALAMAT:</label>
                        <textarea name="teacher_address" required><?php echo $row['teacher_address']; ?></textarea>
                    </div>
                    <div class="class-info">
                        <br>
                        <hr>
                        <h3>GURU KELAS:</h3>
                        <input type="text" value="<?php echo strtoupper($row['class_name']); ?>" readonly>
                        <br>
                        <hr>
                        <div class="action-buttons">
                            <button class="yellow">SIMPAN</button>
                            <input type="button" class="yellow" onClick="location.href='teacher_dashboard.php';" value="DASHBOARD">
                        </div>
                    </div>
                </section>
            </form>
        </main>
    </div>
</body>

</html>
</body>

</html>

</html>