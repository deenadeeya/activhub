<?php
require_once 'connect.php';

ini_set('session.gc_maxlifetime', 1800); // 30 minutes
session_set_cookie_params(1800);
session_start();

// Auto-login using cookies
if (isset($_COOKIE['user_ic']) && isset($_COOKIE['user_role'])) {
  $_SESSION['user_ic'] = $_COOKIE['user_ic'];
  $_SESSION['user_role'] = $_COOKIE['user_role'];
  $_SESSION['LAST_ACTIVITY'] = time(); // reset activity timer

  if ($_COOKIE['user_role'] === 'admin') {
    header("Location: ../admin/admin_dashboard.php");
    exit();
  } elseif ($_COOKIE['user_role'] === 'teacher') {
    header("Location: ../teacher/teacher_dashboard.php");
    exit();
  } elseif ($_COOKIE['user_role'] === 'student') {
    header("Location: student_dashboard.php");
    exit();
  }
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $remember_me = isset($_POST["remember"]);

    // Admin login (by username)
    $sql = "SELECT * FROM admin WHERE uname_admin = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) === 1) {
      $admin = mysqli_fetch_assoc($result);
      if (password_verify($password, $admin['pass_admin'])) {
        $_SESSION['user_ic'] = $admin['uname_admin'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['LAST_ACTIVITY'] = time();

        if ($remember_me) {
          setcookie("user_ic", $admin['uname_admin'], time() + (86400 * 30), "/");
          setcookie("user_role", "admin", time() + (86400 * 30), "/");
        }

        header("Location: ../admin/admin_dashboard.php");
        exit();
      }
    }

    // Teacher login (by IC or username)
    $sql = "SELECT * FROM teacher WHERE teacher_ic = ? OR teacher_uname = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) === 1) {
      $teacher = mysqli_fetch_assoc($result);
      if (password_verify($password, $teacher['teacher_pass'])) {
        $_SESSION['user_ic'] = $teacher['teacher_ic'];
        $_SESSION['user_role'] = 'teacher';
        $_SESSION['LAST_ACTIVITY'] = time();

        if ($remember_me) {
          setcookie("user_ic", $teacher['teacher_ic'], time() + (86400 * 30), "/");
          setcookie("user_role", "teacher", time() + (86400 * 30), "/");
        }

        header("Location: ../teacher/teacher_dashboard.php");
        exit();
      }
    }

    // Student login (by IC)
    $sql = "SELECT * FROM student WHERE student_ic = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) === 1) {
      $student = mysqli_fetch_assoc($result);
      if (password_verify($password, $student['student_pass'])) {
        $_SESSION['user_ic'] = $student['student_ic'];
        $_SESSION['user_role'] = 'student';
        $_SESSION['LAST_ACTIVITY'] = time();

        if ($remember_me) {
          setcookie("user_ic", $student['student_ic'], time() + (86400 * 30), "/");
          setcookie("user_role", "student", time() + (86400 * 30), "/");
        }

        header("Location: student_dashboard.php");
        exit();
      }
    }

    $error = "Invalid username or password.";
  } else {
    $error = "Please enter both username and password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Log Masuk - SRIAAWP</title>
  <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../css/login.css" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
</head>

<body>
  <div class="container">
    <div class="header-block">
      <div class="header-row">
        <img src="img/logo.png" alt="School Logo" class="logo" />
        <div class="title-text">
          <p class="welcome">Selamat Datang Ke</p>
          <h1>SRIAAWP ActivHub</h1>
        </div>
      </div>
      <p class="subtitle">“Pusat Rekod Kokurikulum Pelajar SRI AL-AMIN WILAYAH PERSEKUTUAN”</p>
      <p class="subtitle">“ڤوست ريكود كوكوريكولوم موريد-موريد سري الأمين ولايه ڤرسكوتوان”</p>

      <div class="login-box">
        <h2>السلام عليكم</h2>
        <p class="login-subtext">Sila Log Masuk</p>
        <form action="login.php" method="post">
          <input type="text" name="username" placeholder="Username / IC Number"
            value="<?php echo isset($_COOKIE['user_ic']) ? htmlspecialchars($_COOKIE['user_ic']) : ''; ?>" required />

          <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Password" required />
            <span class="material-symbols-outlined toggle-password" onclick="togglePassword()">visibility_off</span>
          </div>

          <div class="remember">
            <label><input type="checkbox" name="remember"> Remember Me</label>
          </div>

          <br><br>
          <button type="submit" name="login">LOG MASUK</button>

          <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
          <?php if (isset($_GET['expired'])) echo "<p style='color:red;'>Session expired. Please login again.</p>"; ?>
        </form>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById("password");
      const icon = document.querySelector(".toggle-password");
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.textContent = "visibility";
      } else {
        passwordInput.type = "password";
        icon.textContent = "visibility_off";
      }
    }
  </script>
</body>

</html>