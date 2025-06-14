<?php
session_start();
include '../connect.php';
include '../header.php';

// Check if logged in and role is teacher
if (!isset($_SESSION['user_ic']) || $_SESSION['user_role'] !== 'teacher') {
    echo "Unauthorized access. Please <a href='../login.php'>login again</a>.";
    exit;
}

$teacher_ic = $_SESSION['user_ic'];

// Get teacher's class id for notification count
$teacher_class_id = null;
$class_query = "SELECT class_id FROM class WHERE head_teacher = ?";
$stmt = $conn->prepare($class_query);
$stmt->bind_param("s", $teacher_ic);
$stmt->execute();
$class_result = $stmt->get_result();
if ($class_result && $class_result->num_rows > 0) {
    $class_row = $class_result->fetch_assoc();
    $teacher_class_id = $class_row['class_id'];
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = mysqli_real_escape_string($conn, $_POST['teacher_fname']);
    $uname = mysqli_real_escape_string($conn, $_POST['teacher_uname']);
    $contact = mysqli_real_escape_string($conn, $_POST['teacher_contact']);
    $email = mysqli_real_escape_string($conn, $_POST['teacher_email']);
    $dob = $_POST['teacher_dob'];
    $doe = $_POST['teacher_doe'];
    $address = mysqli_real_escape_string($conn, $_POST['teacher_address']);

    // Handle profile picture upload
    $profilePic = $_FILES['teacher_pic'];
    $uploadDir = "../img/uploads/";
    $imagePath = "";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!empty($profilePic['name'])) {
        $targetFile = $uploadDir . basename($profilePic["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $check = getimagesize($profilePic["tmp_name"]);

        if ($check !== false) {
            if (move_uploaded_file($profilePic["tmp_name"], $targetFile)) {
                $imagePath = "img/uploads/" . basename($profilePic["name"]);
            } else {
                echo "<p style='color:red;'>Sorry, there was an error uploading your file.</p>";
            }
        } else {
            echo "<p style='color:red;'>File is not a valid image.</p>";
        }
    }

    // Build update query
    $updateQuery = "UPDATE teacher SET 
        teacher_fname = '$fname',
        teacher_uname = '$uname',
        teacher_contact = '$contact',
        teacher_email = '$email',
        teacher_dob = '$dob',
        teacher_doe = '$doe',
        teacher_address = '$address'";

    if ($imagePath !== "") {
        $updateQuery .= ", teacher_pic = '$imagePath'";
    }

    $updateQuery .= " WHERE teacher_ic = '$teacher_ic'";

    mysqli_query($conn, $updateQuery);
}

// Pending approval count
$pending_count = 0;
if ($teacher_class_id) {
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

// Fetch teacher profile with class info
$query = "
    SELECT t.*, c.class_name 
    FROM teacher t 
    LEFT JOIN class c ON c.head_teacher = t.teacher_ic 
    WHERE t.teacher_ic = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $teacher_ic);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "Tiada Rekod.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Guru - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../css/profile.css" />
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico" />
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
                <span class="admin-text"><?php echo strtoupper(htmlspecialchars($row['teacher_fname'])); ?></span><br>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
                <button onclick="location.href='../approve_form.php'" style="position: relative; background: none; border: none; cursor: pointer;">
                    <span class="material-symbols-outlined icon" style="font-size: 28px; color: white;">
                    notifications
                    </span>
                    <?php if ($pending_count > 0): ?>
                    <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 4px 7px; font-size: 12px;">
                        <?php echo $pending_count; ?>
                    </span>
                    <?php endif; ?>
                </button>
        </div>
    </header>

    <div class="container">
        <h1 class="profile-title">PROFIL GURU</h1>

        <main class="profile-container">
            <form method="POST" enctype="multipart/form-data">
                <section class="left-card">
                    <div class="profile-header">
                        <div class="pic-container">
                            <img src="../<?php echo !empty($row['teacher_pic']) ? htmlspecialchars($row['teacher_pic']) : 'img/profile.jpg'; ?>" alt="Profile Image" class="profile-pic" />
                            <div class="upload-btn">
                                <!-- <label for="teacher_pic">GAMBAR PROFIL:</label> -->
                                <input type="file" name="teacher_pic" id="teacher_pic" accept="image/*" />
                            </div>
                        </div>
                        <!-- <h2>Selamat Datang,<br><span><?php echo strtoupper(htmlspecialchars($row['teacher_fname'])); ?></span></h2> -->
                    </div>

                    <div class="info-group">
                        <label>NAMA PENUH:</label>
                        <input type="text" name="teacher_fname" value="<?php echo htmlspecialchars($row['teacher_fname']); ?>" required />

                        <label class="no-asterisk">NOMBOR IC:</label>
                        <input type="text" name="teacher_ic" value="<?php echo htmlspecialchars($row['teacher_ic']); ?>" readonly />

                        <label>USERNAME / NAMA PENGGUNA:</label>
                        <input type="text" name="teacher_uname" value="<?php echo htmlspecialchars($row['teacher_uname']); ?>" required />

                        <label>NOMBOR TELEFON:</label>
                        <input type="text" name="teacher_contact" value="<?php echo htmlspecialchars($row['teacher_contact']); ?>" required />

                        <label>EMEL:</label>
                        <input type="email" name="teacher_email" value="<?php echo htmlspecialchars($row['teacher_email']); ?>" required />

                        <label>TARIKH LAHIR:</label>
                        <input type="date" name="teacher_dob" value="<?php echo htmlspecialchars($row['teacher_dob']); ?>" required />

                        <label>TARIKH MASUK KERJA:</label>
                        <input type="date" name="teacher_doe" value="<?php echo htmlspecialchars($row['teacher_doe']); ?>" required />

                        <label>ALAMAT:</label>
                        <textarea name="teacher_address" required><?php echo htmlspecialchars($row['teacher_address']); ?></textarea>
                    </div>

                    <div class="class-info">
                        <br />
                        <hr />
                        <h3 class="no-asterisk">GURU KELAS:</h3>
                        <input type="text" value="<?php echo !empty($row['class_name']) ? strtoupper(htmlspecialchars($row['class_name'])) : 'TIADA KELAS DIPILIH'; ?>" readonly />
                        <br />
                        <hr />
                        <div class="action-buttons">
                            <button class="yellow" type="submit">SIMPAN</button>
                            <input type="button" class="yellow" onClick="location.href='teacher_dashboard.php';" value="DASHBOARD" />
                        </div>
                    </div>
                </section>
            </form>
        </main>
    </div>
</body>

</html>