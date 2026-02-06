<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 3600); // 1 hour
    session_set_cookie_params(3600);
    session_start();
}

$inactive = 300; // 5 minutes

if (isset($_SESSION['timeout']) && (time() - $_SESSION['timeout'] > $inactive)) {
    session_unset();
    session_destroy();
    header("Location: logout.php?timeout=true");
    exit();
}

$_SESSION['timeout'] = time();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
