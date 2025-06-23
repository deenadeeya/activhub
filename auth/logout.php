<?php
session_start();
session_unset();
session_destroy();

// Delete the cookies
setcookie('user_ic', '', time() - 3600, "/");
setcookie('user_role', '', time() - 3600, "/");

header("Location: ../index.php");
exit();
?>