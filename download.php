<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get wallpaper ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    if ($id > 0) {
        // Log the download request
        error_log("Processing download request for wallpaper ID: " . $id);
        
        // Get wallpaper details
        $stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE id = ?");
        $stmt->execute([$id]);
        $wallpaper = $stmt->fetch();
        
        if ($wallpaper) {
            // Log the wallpaper details
            error_log("Found wallpaper: " . $wallpaper['title']);
            
            // Get file path
            $file_path = 'uploads/' . $wallpaper['file_path'];
            
            // Check if file exists
            if (file_exists($file_path)) {
                // Log the file path
                error_log("File exists: " . $file_path);
                
                // Set headers for download
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($wallpaper['file_path']) . '"');
                header('Content-Length: ' . filesize($file_path));
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                // Output file
                readfile($file_path);
                exit;
            } else {
                // Log the error
                error_log("File not found: " . $file_path);
                
                // Redirect to error page
                header('Location: error.php?message=File not found');
                exit;
            }
        } else {
            // Log the error
            error_log("Wallpaper not found: " . $id);
            
            // Redirect to error page
            header('Location: error.php?message=Wallpaper not found');
            exit;
        }
    } else {
        // Log the error
        error_log("Invalid wallpaper ID: " . $id);
        
        // Redirect to error page
        header('Location: error.php?message=Invalid wallpaper ID');
        exit;
    }
} catch (PDOException $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // Redirect to error page
    header('Location: error.php?message=Database error occurred');
    exit;
} catch (Exception $e) {
    // Log the error
    error_log("General error: " . $e->getMessage());
    
    // Redirect to error page
    header('Location: error.php?message=An error occurred while processing your request');
    exit;
} 