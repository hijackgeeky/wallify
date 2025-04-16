<?php
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get current admin info
if (isset($_SESSION['admin_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $current_admin = $stmt->fetch();
    
    if (!$current_admin) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
?>