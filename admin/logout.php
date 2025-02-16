<?php
session_start();
session_destroy();

// Clear admin cookies
setcookie('admin_last_login', '', time() - 3600, '/');

header("Location: login.php");
exit(); 