<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

try {
    // Get and validate search query
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (strlen($query) < 2) {
        echo json_encode([]);
        exit;
    }

    // Prepare the database query
    $searchTerm = "%{$query}%";
    
    // Search in both products and categories
    $sql = "SELECT 
                'product' as type,
                id,
                title as name,
                category,
                thumbnail as image
            FROM wallpapers 
            WHERE (title LIKE :term OR description LIKE :term OR category LIKE :term)
                AND status = 'active'
            UNION
            SELECT 
                'category' as type,
                id,
                name,
                NULL as category,
                NULL as image
            FROM categories 
            WHERE name LIKE :term
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':term', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process results
    foreach ($results as &$result) {
        // Ensure image URLs are complete
        if ($result['image']) {
            $result['image'] = get_image_url($result['image']);
        }
        
        // Sanitize output
        $result['name'] = htmlspecialchars($result['name']);
        if ($result['category']) {
            $result['category'] = htmlspecialchars($result['category']);
        }
    }
    
    echo json_encode($results);

} catch (PDOException $e) {
    error_log("Search suggestion error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred while searching'
    ]);
} catch (Exception $e) {
    error_log("General error in search suggestions: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while processing your search'
    ]);
} 