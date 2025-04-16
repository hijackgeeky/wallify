<?php
require_once 'includes/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
error_log("Search request received: " . print_r($_GET, true));

// Get search query
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if (!empty($search)) {
        // Log the search query
        error_log("Processing search query: " . $search);
        
        // Prepare search query with proper filtering
        $sql = "SELECT * FROM wallpapers WHERE 
                LOWER(title) LIKE LOWER(:search) OR 
                LOWER(description) LIKE LOWER(:search) OR 
                LOWER(category) LIKE LOWER(:search) 
                ORDER BY created_at DESC LIMIT 10";
        
        // Log the SQL query
        error_log("Executing SQL: " . $sql);
        
        $stmt = $pdo->prepare($sql);
        $search_param = "%{$search}%";
        $stmt->execute(['search' => $search_param]);
        
        // Log the number of results
        $results = $stmt->fetchAll();
        error_log("Found " . count($results) . " results");
        
        if (count($results) > 0) {
            echo "<div class='search-results'>";
            foreach ($results as $row) {
                echo "<div class='suggestion-item' data-id='" . htmlspecialchars($row['id']) . "'>";
                echo "<i class='bi bi-image'></i>";
                echo "<span>" . htmlspecialchars($row['title']) . "</span>";
                echo "<small class='text-muted'>" . htmlspecialchars($row['category']) . "</small>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='suggestion-item'>No results found</div>";
        }
    } else {
        error_log("Empty search query received");
        echo "<div class='suggestion-item'>Please enter at least 2 characters</div>";
    }
} catch (PDOException $e) {
    error_log("Database Error in search: " . $e->getMessage());
    error_log("Error Code: " . $e->getCode());
    echo "<div class='suggestion-item text-danger'>Database error occurred</div>";
} catch (Exception $e) {
    error_log("General Error in search: " . $e->getMessage());
    echo "<div class='suggestion-item text-danger'>An unexpected error occurred</div>";
}
?> 