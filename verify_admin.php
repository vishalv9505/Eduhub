<?php
require_once 'config/database.php';

try {
    // Check if admin user exists
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Admin user exists in database:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Password Hash: " . $user['password'] . "<br>";
        
        // Test password verification
        $test_password = 'admin123';
        if (password_verify($test_password, $user['password'])) {
            echo "<br>Password verification successful!<br>";
            echo "The password 'admin123' matches the stored hash.";
        } else {
            echo "<br>Password verification failed!<br>";
            echo "The password 'admin123' does not match the stored hash.<br>";
            
            // Create new admin user with correct password
            echo "<br>Creating new admin user with correct password...<br>";
            
            // Delete existing admin user
            $stmt = $conn->prepare("DELETE FROM admin_users WHERE username = 'admin'");
            $stmt->execute();
            
            // Create new admin user
            $username = 'admin';
            $password = 'admin123';
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            
            echo "New admin user created successfully!<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        }
    } else {
        echo "Admin user not found in database!<br>";
        echo "Please run setup_admin.php to create the admin user.";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 