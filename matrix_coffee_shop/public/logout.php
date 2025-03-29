<?php
// public/logout.php
session_start();
require_once('../includes/auth.php');
require_once('../includes/functions.php');

// Log de uitlog actie (indien ingelogd)
if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'logout', 'User logged out');
}

// Verwijder alle sessiedata
logout();

// Doorsturen naar homepage
header("Location: index.php?logout=success");
exit;
?>
