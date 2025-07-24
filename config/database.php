<?php
// Database configuration
$host = 'localhost';
$dbname = 'eduhub_db';
$username = 'root';
$password = '';

// Create database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Export configuration
return [
    'host' => $host,
    'dbname' => $dbname,
    'username' => $username,
    'password' => $password
]; 