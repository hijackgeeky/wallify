<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

try {
    // Test database connection
    echo "Testing database connection...<br>";
    $pdo->query("SELECT 1");
    echo "Database connection successful!<br><br>";

    // Check if wallpapers table exists
    echo "Checking wallpapers table...<br>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'wallpapers'");
    if ($stmt->rowCount() > 0) {
        echo "Wallpapers table exists!<br><br>";
        
        // Show table structure
        echo "Table structure:<br>";
        $stmt = $pdo->query("DESCRIBE wallpapers");
        echo "<pre>";
        print_r($stmt->fetchAll());
        echo "</pre>";
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM wallpapers");
        $count = $stmt->fetch()['count'];
        echo "Total wallpapers: " . $count . "<br>";
        
        // Show sample records
        if ($count > 0) {
            echo "<br>Sample records:<br>";
            $stmt = $pdo->query("SELECT id, title, category FROM wallpapers LIMIT 3");
            echo "<pre>";
            print_r($stmt->fetchAll());
            echo "</pre>";
        }
    } else {
        echo "Wallpapers table does not exist!<br>";
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "<br>";
}
?> 