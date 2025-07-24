<?php
session_start();
require_once 'includes/functions.php';

if (isset($_SESSION['admin_id'])) {
    // Log logout action
    logAdminAction($_SESSION['admin_id'], 'logout', 'Admin logged out');
    
    // Clear session
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: login.php");
exit();
?> 