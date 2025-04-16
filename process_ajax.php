<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Get token from either POST or GET
$token = isset($_POST['_token']) ? $_POST['_token'] : (isset($_GET['_token']) ? $_GET['_token'] : null);

// Verify CSRF token
if (!$token || $token !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Initialize response array
$response = ['status' => 'error', 'message' => 'Invalid action'];

try {
    // Get action from either POST or GET
    $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : null);
    
    // Handle different actions
    if ($action) {
        switch ($action) {
            case 'add_wallpaper':
                if (!empty($_FILES['wallpaper'])) {
                    $upload_result = uploadWallpaper($_FILES['wallpaper']);
                    
                    if ($upload_result['success']) {
                        // Get category name from ID
                        $category_id = (int)$_POST['category'];
                        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                        $stmt->execute([$category_id]);
                        $category = $stmt->fetch();
                        
                        if (!$category) {
                            $response = ['status' => 'error', 'message' => 'Invalid category selected'];
                            break;
                        }
                        
                        $stmt = $pdo->prepare("INSERT INTO wallpapers 
                            (title, description, category, file_path, thumbnail_path, is_featured, is_premium, created_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        
                        $stmt->execute([
                            sanitize($_POST['title']),
                            sanitize($_POST['description']),
                            $category['name'],
                            $upload_result['file_path'],
                            $upload_result['thumbnail_path'],
                            isset($_POST['is_featured']) ? 1 : 0,
                            isset($_POST['is_premium']) ? 1 : 0,
                            $_SESSION['admin_id']
                        ]);
                        
                        $_SESSION['flash_message'] = 'Wallpaper added successfully!';
                        $_SESSION['flash_type'] = 'success';
                        $response = [
                            'status' => 'success',
                            'message' => 'Wallpaper added successfully!',
                            'redirect' => 'admin.php?content=wallpapers'
                        ];
                    } else {
                        $_SESSION['flash_message'] = $upload_result['message'];
                        $_SESSION['flash_type'] = 'danger';
                        $response = ['status' => 'error', 'message' => $upload_result['message']];
                    }
                } else {
                    $_SESSION['flash_message'] = 'Please select an image';
                    $_SESSION['flash_type'] = 'danger';
                    $response = ['status' => 'error', 'message' => 'Please select an image'];
                }
                break;
                
            case 'update_wallpaper':
                // Check if ID exists
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    $response = ['status' => 'error', 'message' => 'Wallpaper ID is required'];
                    break;
                }
                
                $id = (int)$_POST['id'];
                
                // Debug log
                error_log("Update Wallpaper - is_featured: " . (isset($_POST['is_featured']) ? $_POST['is_featured'] : 'not set'));
                error_log("Update Wallpaper - is_premium: " . (isset($_POST['is_premium']) ? $_POST['is_premium'] : 'not set'));
                
                // Get category name from ID
                $category_id = (int)$_POST['category'];
                $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                $category = $stmt->fetch();
                
                if (!$category) {
                    $_SESSION['flash_message'] = 'Invalid category selected';
                    $_SESSION['flash_type'] = 'danger';
                    $response = ['status' => 'error', 'message' => 'Invalid category selected'];
                    break;
                }
                
                // Prepare data array with sanitized inputs
                $data = [
                    'title' => sanitize($_POST['title']),
                    'description' => sanitize($_POST['description']),
                    'category' => $category['name'],
                    'is_featured' => (isset($_POST['is_featured']) && $_POST['is_featured'] == '1') ? 1 : 0,
                    'is_premium' => (isset($_POST['is_premium']) && $_POST['is_premium'] == '1') ? 1 : 0
                ];
                
                // Debug log the final values
                error_log("Final data - is_featured: " . $data['is_featured']);
                error_log("Final data - is_premium: " . $data['is_premium']);
                
                // Use the updateWallpaper function
                $file = !empty($_FILES['wallpaper']['name']) ? $_FILES['wallpaper'] : null;
                $result = updateWallpaper($id, $data, $file, $pdo);
                
                if ($result['success']) {
                    $_SESSION['flash_message'] = $result['message'];
                    $_SESSION['flash_type'] = 'success';
                    $response = [
                        'status' => 'success',
                        'message' => $result['message'],
                        'redirect' => 'admin.php?content=wallpapers',
                        'thumbnail_path' => $result['thumbnail_path']
                    ];
                } else {
                    $_SESSION['flash_message'] = $result['message'];
                    $_SESSION['flash_type'] = 'danger';
                    $response = ['status' => 'error', 'message' => $result['message']];
                }
                break;
                
            case 'delete_wallpaper':
                // Get ID from POST or GET parameters
                $id = !empty($_POST['id']) ? $_POST['id'] : (!empty($_GET['id']) ? $_GET['id'] : null);
                $return_url = isset($_GET['return_url']) ? $_GET['return_url'] : 'admin.php?content=wallpapers';
                
                if ($id) {
                    if (deleteWallpaper($id, $pdo)) {
                        // For simplicity, just redirect back to the wallpapers page
                        $_SESSION['flash_message'] = 'Wallpaper deleted successfully';
                        $_SESSION['flash_type'] = 'success';
                        
                        header('Location: ' . $return_url);
                        exit;
                    } else {
                        $_SESSION['flash_message'] = 'Wallpaper not found';
                        $_SESSION['flash_type'] = 'danger';
                        
                        header('Location: ' . $return_url);
                        exit;
                    }
                } else {
                    $_SESSION['flash_message'] = 'No wallpaper ID provided';
                    $_SESSION['flash_type'] = 'danger';
                    
                    header('Location: ' . $return_url);
                    exit;
                }
                break;
                
            case 'add_admin':
                if (!isset($_POST['username']) || empty($_POST['username']) || 
                    !isset($_POST['email']) || empty($_POST['email']) || 
                    !isset($_POST['password']) || empty($_POST['password'])) {
                    $response = ['status' => 'error', 'message' => 'All fields are required'];
                    break;
                }
                
                $password_validation = validatePassword($_POST['password']);
                if ($password_validation === true) {
                    try {
                        // Check if username already exists
                        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
                        $stmt->execute([sanitize($_POST['username'])]);
                        if ($stmt->fetch()) {
                            $response = ['status' => 'error', 'message' => 'Username already exists. Please choose a different username.'];
                            break;
                        }

                        // Check if email already exists
                        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
                        $stmt->execute([sanitize($_POST['email'])]);
                        if ($stmt->fetch()) {
                            $response = ['status' => 'error', 'message' => 'Email already exists. Please use a different email address.'];
                            break;
                        }

                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
                        if ($stmt->execute([
                        sanitize($_POST['username']),
                        sanitize($_POST['email']),
                        $hashed_password
                        ])) {
                            $_SESSION['flash_message'] = 'Admin added successfully!';
                            $_SESSION['flash_type'] = 'success';
                            $response = [
                                'status' => 'success',
                                'message' => 'Admin added successfully!',
                                'redirect' => 'admin.php?content=admins'
                            ];
                        } else {
                            $_SESSION['flash_message'] = 'Failed to add admin. Please try again.';
                            $_SESSION['flash_type'] = 'danger';
                            $response = ['status' => 'error', 'message' => 'Failed to add admin. Please try again.'];
                        }
                    } catch (PDOException $e) {
                        // Handle specific database errors
                        if ($e->getCode() == 23000) { // Duplicate entry error
                            if (strpos($e->getMessage(), 'username') !== false) {
                                $response = ['status' => 'error', 'message' => 'Username already exists. Please choose a different username.'];
                            } elseif (strpos($e->getMessage(), 'email') !== false) {
                                $response = ['status' => 'error', 'message' => 'Email already exists. Please use a different email address.'];
                            } else {
                                $response = ['status' => 'error', 'message' => 'This record already exists. Please try again with different information.'];
                            }
                        } else {
                            $response = ['status' => 'error', 'message' => 'An error occurred while adding the admin. Please try again.'];
                        }
                    }
                } else {
                    $response = ['status' => 'error', 'message' => $password_validation];
                }
                break;
                
            case 'update_admin':
                if (!isset($_POST['id']) || empty($_POST['id']) || 
                    !isset($_POST['username']) || empty($_POST['username']) || 
                    !isset($_POST['email']) || empty($_POST['email'])) {
                    $response = ['status' => 'error', 'message' => 'Required fields are missing'];
                    break;
                }

                $id = (int)$_POST['id'];
                $username = sanitize($_POST['username']);
                $email = sanitize($_POST['email']);
                
                try {
                    // Check if username or email already exists for other admins
                    $stmt = $pdo->prepare("SELECT id FROM admins WHERE (username = ? OR email = ?) AND id != ?");
                    $stmt->execute([$username, $email, $id]);
                    if ($stmt->fetch()) {
                        $response = ['status' => 'error', 'message' => 'Username or email already exists'];
                        break;
                    }

                    // Update admin
                    $sql = "UPDATE admins SET username = ?, email = ?";
                    $params = [$username, $email];

                    // If password is provided, update it too
                    if (!empty($_POST['password'])) {
                        $password_validation = validatePassword($_POST['password']);
                        if ($password_validation === true) {
                            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                            $sql .= ", password = ?";
                            $params[] = $hashed_password;
                        } else {
                            $response = ['status' => 'error', 'message' => $password_validation];
                            break;
                        }
                    }

                    $sql .= " WHERE id = ?";
                    $params[] = $id;

                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute($params)) {
                        $_SESSION['flash_message'] = 'Admin updated successfully!';
                        $_SESSION['flash_type'] = 'success';
                        $response = [
                            'status' => 'success',
                            'message' => 'Admin updated successfully!',
                            'redirect' => 'admin.php?content=admins'
                        ];
                    } else {
                        $_SESSION['flash_message'] = 'Failed to update admin';
                        $_SESSION['flash_type'] = 'danger';
                        $response = ['status' => 'error', 'message' => 'Failed to update admin'];
                    }
                } catch (PDOException $e) {
                    $_SESSION['flash_message'] = 'Database error: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                    $response = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
                }
                break;

            case 'delete_admin':
                $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);
                $return_url = isset($_GET['return_url']) ? $_GET['return_url'] : 'admin.php?content=admins';
                
                if (!$id) {
                    $_SESSION['flash_message'] = 'Admin ID is required';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . $return_url);
                    exit;
                }

                try {
                    // Check if admin exists
                    $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ?");
                    $stmt->execute([$id]);
                    if (!$stmt->fetch()) {
                        $_SESSION['flash_message'] = 'Admin not found';
                        $_SESSION['flash_type'] = 'danger';
                        header('Location: ' . $return_url);
                        exit;
                    }

                    // Delete admin
                    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        $_SESSION['flash_message'] = 'Admin deleted successfully!';
                        $_SESSION['flash_type'] = 'success';
                        header('Location: ' . $return_url);
                        exit;
                    } else {
                        $_SESSION['flash_message'] = 'Failed to delete admin';
                        $_SESSION['flash_type'] = 'danger';
                        header('Location: ' . $return_url);
                        exit;
                    }
                } catch (PDOException $e) {
                    $_SESSION['flash_message'] = 'An error occurred while deleting the admin';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . $return_url);
                    exit;
                }
                break;
                
            case 'add_category':
                if (!isset($_POST['name']) || empty($_POST['name'])) {
                    $response = ['status' => 'error', 'message' => 'Category name is required'];
                    break;
                }

                $name = trim($_POST['name']);
                $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;

                // Check if category already exists
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                $stmt->execute([$name]);
                if ($stmt->fetch()) {
                    $response = ['status' => 'error', 'message' => 'Category already exists'];
                    break;
                }

                // Insert new category
                $stmt = $pdo->prepare("INSERT INTO categories (name, display_order) VALUES (?, ?)");
                if ($stmt->execute([$name, $display_order])) {
                    $_SESSION['flash_message'] = 'Category added successfully';
                    $_SESSION['flash_type'] = 'success';
                    $response = [
                        'status' => 'success',
                        'message' => 'Category added successfully'
                    ];
                } else {
                    $_SESSION['flash_message'] = 'Failed to add category';
                    $_SESSION['flash_type'] = 'danger';
                    $response = ['status' => 'error', 'message' => 'Failed to add category'];
                }
                break;
                
            case 'update_category':
                if (!isset($_POST['id']) || !isset($_POST['name']) || empty($_POST['name'])) {
                    $response = ['status' => 'error', 'message' => 'Category ID and name are required'];
                    break;
                }

                $id = (int)$_POST['id'];
                $name = trim($_POST['name']);
                $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;

                // Check if category exists
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    $response = ['status' => 'error', 'message' => 'Category not found'];
                    break;
                }

                // Check if name is already taken by another category
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
                $stmt->execute([$name, $id]);
                if ($stmt->fetch()) {
                    $response = ['status' => 'error', 'message' => 'Category name already exists'];
                    break;
                }

                // Update category
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, display_order = ? WHERE id = ?");
                if ($stmt->execute([$name, $display_order, $id])) {
                    $_SESSION['flash_message'] = 'Category updated successfully';
                    $_SESSION['flash_type'] = 'success';
                    $response = ['status' => 'success', 'message' => 'Category updated successfully'];
                } else {
                    $_SESSION['flash_message'] = 'Failed to update category';
                    $_SESSION['flash_type'] = 'danger';
                    $response = ['status' => 'error', 'message' => 'Failed to update category'];
                }
                break;
                
            case 'delete_category':
                $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);

                if (!$id) {
                    $_SESSION['flash_message'] = 'Category ID is required';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: admin.php?content=categories');
                    exit;
                }

                // Check if category exists
                $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $category = $stmt->fetch();
                
                if (!$category) {
                    $_SESSION['flash_message'] = 'Category not found';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: admin.php?content=categories');
                    exit;
                }

                // Start a transaction
                $pdo->beginTransaction();
                
                try {
                    // First, delete all wallpapers associated with this category
                    $stmt = $pdo->prepare("SELECT id, file_path, thumbnail_path FROM wallpapers WHERE category = ?");
                    $stmt->execute([$category['name']]);
                    $wallpapers = $stmt->fetchAll();
                    
                    foreach ($wallpapers as $wallpaper) {
                        // Delete the actual files
                        if (file_exists($wallpaper['file_path'])) {
                            unlink($wallpaper['file_path']);
                        }
                        if (file_exists($wallpaper['thumbnail_path'])) {
                            unlink($wallpaper['thumbnail_path']);
                        }
                        
                        // Delete from database
                        $stmt = $pdo->prepare("DELETE FROM wallpapers WHERE id = ?");
                        $stmt->execute([$wallpaper['id']]);
                    }
                    
                    // Now delete the category
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    // Commit the transaction
                    $pdo->commit();
                    
                    $_SESSION['flash_message'] = 'Category and associated wallpapers deleted successfully';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: admin.php?content=categories');
                    exit;
                } catch (Exception $e) {
                    // Rollback the transaction on error
                    $pdo->rollBack();
                    
                    $_SESSION['flash_message'] = 'Failed to delete category: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: admin.php?content=categories');
                    exit;
                }
                break;
                
            // Add more actions as needed
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>