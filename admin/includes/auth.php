<?php
session_start();

if(!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check last activity time
$inactive = 1800; // 30 minutes
if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_destroy();
    header("Location: login.php?msg=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role'];

// Optional: Check if IP has changed
if(isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_destroy();
    header("Location: login.php?msg=ip_changed");
    exit();
}
$_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR']; 