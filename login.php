<?php
require_once 'connect.php';
session_start();

// Check if cookies are set and auto-login the user
if (isset($_COOKIE['user_ic']) && isset($_COOKIE['user_role'])) {
  $user_ic = $_COOKIE['user_ic'];
  $user_role = $_COOKIE['user_role'];

  // Set session variables based on cookie values
  $_SESSION['user_ic'] = $user_ic;
  $_SESSION['user_role'] = $user_role;

  // Redirect based on the user's role
  if ($user_role === 'admin') {
    header("Location: ../admin/admin_dashboard.php");
    exit();
  } elseif ($user_role === 'teacher') {
    header("Location: ../teacher/teacher_dashboard.php");
    exit();
  } elseif ($user_role === 'student') {
    header("Location: student_dashboard.php");
    exit();
  }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $remember_me = isset($_POST["remember"]) ? true : false;

    // Admin 
    $sql_admin = "SELECT * FROM admin WHERE uname_admin='$username'";
    $result_admin = mysqli_query($conn, $sql_admin);
    if ($result_admin && mysqli_num_rows($result_admin) === 1) {
      $admin = mysqli_fetch_assoc($result_admin);
      if (password_verify($password, $admin['pass_admin'])) {
        $_SESSION['user_ic'] = $admin['uname_admin'];
        $_SESSION['user_role'] = 'admin';


        if ($remember_me) {
          setcookie('user_ic', $admin['uname_admin'], time() + (86400 * 30), "/");  // 30 days cookie
          setcookie('user_role', 'admin', time() + (86400 * 30), "/");
        }

        header("Location: ../admin/admin_dashboard.php");
        exit();
      }
    }

    // Teacher
    $sql_teacher = "SELECT * FROM teacher WHERE teacher_ic='$username'";
    $result_teacher = mysqli_query($conn, $sql_teacher);
    if ($result_teacher && mysqli_num_rows($result_teacher) === 1) {
      $teacher = mysqli_fetch_assoc($result_teacher);
      if (password_verify($password, $teacher['teacher_pass'])) {
        $_SESSION['user_ic'] = $teacher['teacher_ic'];
        $_SESSION['user_role'] = 'teacher';


        if ($remember_me) {
          setcookie('user_ic', $teacher['teacher_ic'], time() + (86400 * 30), "/");  // 30 days cookie
          setcookie('user_role', 'teacher', time() + (86400 * 30), "/");
        }

        header("Location: ../teacher/teacher_dashboard.php");
        exit();
      }
    }

    // Student 
    $sql_student = "SELECT * FROM student WHERE student_ic='$username'";
    $result_student = mysqli_query($conn, $sql_student);
    if ($result_student && mysqli_num_rows($result_student) === 1) {
      $student = mysqli_fetch_assoc($result_student);
      if (password_verify($password, $student['student_pass'])) {
        $_SESSION['user_ic'] = $student['student_ic'];
        $_SESSION['user_role'] = 'student';

        if ($remember_me) {
          setcookie('user_ic', $student['student_ic'], time() + (86400 * 30), "/");  // 30 days cookie
          setcookie('user_role', 'student', time() + (86400 * 30), "/");
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
  <title>SRI AL-AMIN ActivHub</title>
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
          <p class="welcome">Selamat Datang Ke سلامت داتڠ ك</p>
          <h1>SRIAAWP ActivHub</h1>
        </div>
      </div>
      <p class="subtitle">“Pusat Rekod Kokurikulum Pelajar SRI AL-AMIN WILAYAH PERSEKUTUAN”</p>
      <p class="subtitle">“ڤوست ريكود كوكوريكولوم موريد-موريد سري الأمين ولايه ڤرسكوتوان”</p>

      <!-- LOGIN BOX -->
      <div class="login-box">
        <h2>Assalamualaikum</h2>
        <p class="login-subtext">Sila Log Masuk</p>
        <form action="login.php" method="post">
          <input type="text" placeholder="Username / IC Number" name="username"
            value="<?php echo isset($_COOKIE['user_ic']) ? $_COOKIE['user_ic'] : ''; ?>" required />
          <div class="password-wrapper">
            <input type="password" id="password" placeholder="Password" name="password" required />
            <span class="material-symbols-outlined toggle-password" onclick="togglePassword()">
              visibility_off
            </span>
          </div>

          <div class="remember">
            <label>
              <input type="checkbox" name="remember">Remember Me
            </label>
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