<?php
require_once 'includes/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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
        
        // Format results for display
        $formatted_results = array_map(function($item) {
            return [
                'id' => $item['id'],
                'title' => htmlspecialchars($item['title']),
                'description' => htmlspecialchars($item['description']),
                'category' => htmlspecialchars($item['category']),
                'thumbnail_path' => htmlspecialchars($item['thumbnail_path']),
                'is_premium' => (bool)$item['is_premium']
            ];
        }, $results);
        
        // Return results
        echo json_encode([
            'success' => true,
            'query' => $search,
            'count' => count($formatted_results),
            'results' => $formatted_results
        ]);
    } else {
        // Return empty results for empty search
        echo json_encode([
            'success' => true,
            'query' => '',
            'count' => 0,
            'results' => []
        ]);
    }
} catch (PDOException $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Log the error
    error_log("General error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request',
        'error' => $e->getMessage()
    ]);
} 