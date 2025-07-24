<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['admin_id'])) {
    // Store the current URL for redirection after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    // Redirect to login page
    header("Location: /Eduhub/login.php");
}