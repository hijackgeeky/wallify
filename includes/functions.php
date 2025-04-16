<?php
require_once 'config.php';

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadWallpaper($file) {
    if (!extension_loaded('gd')) {
        return ['success' => false, 'message' => 'GD library is not installed'];
    }

    $target_dir = UPLOAD_DIR;
    $thumbnail_dir = THUMBNAIL_DIR;
    
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $imageFileType;
    $target_path = $target_dir . $filename;
    $thumbnail_path = $thumbnail_dir . 'thumb_' . $filename;
    
    // Check if image file is a actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File is too large (max 100MB)'];
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'];
    }
    
    // Upload original image
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => false, 'message' => 'Error uploading file'];
    }
    
    // Create thumbnail
    createThumbnail($target_path, $thumbnail_path, 300, 200);
    
    return [
        'success' => true,
        'file_path' => $filename,
        'thumbnail_path' => 'thumb_' . $filename
    ];
}

function createThumbnail($src, $dest, $targetWidth, $targetHeight) {
    $imageInfo = getimagesize($src);
    $imageType = $imageInfo[2];
    
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($src);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($src);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($src);
            break;
        default:
            return false;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Calculate aspect ratio
    $sourceRatio = $width / $height;
    $targetRatio = $targetWidth / $targetHeight;
    
    if ($sourceRatio > $targetRatio) {
        $newHeight = $targetHeight;
        $newWidth = (int)($targetHeight * $sourceRatio);
    } else {
        $newWidth = $targetWidth;
        $newHeight = (int)($targetWidth / $sourceRatio);
    }
    
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
    
    // Resize and crop
    imagecopyresampled($thumbnail, $image, 
        0 - ($newWidth - $targetWidth) / 2, 
        0 - ($newHeight - $targetHeight) / 2, 
        0, 0, 
        $newWidth, $newHeight, 
        $width, $height);
    
    // Save thumbnail
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $dest, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $dest);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $dest);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($thumbnail);
    
    return true;
}

function deleteWallpaper($id, $pdo) {
    // Get wallpaper data
    $stmt = $pdo->prepare("SELECT file_path, thumbnail_path FROM wallpapers WHERE id = ?");
    $stmt->execute([$id]);
    $wallpaper = $stmt->fetch();
    
    if ($wallpaper) {
        // Delete files
        $image_path = UPLOAD_DIR . $wallpaper['file_path'];
        $thumb_path = THUMBNAIL_DIR . $wallpaper['thumbnail_path'];
        
        if (file_exists($image_path)) unlink($image_path);
        if (file_exists($thumb_path)) unlink($thumb_path);
        
        // Delete from database
        $pdo->prepare("DELETE FROM wallpapers WHERE id = ?")->execute([$id]);
        return true;
    }
    return false;
}

function updateWallpaper($id, $data, $file = null, $pdo) {
    // Get current wallpaper data
    $stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE id = ?");
    $stmt->execute([$id]);
    $wallpaper = $stmt->fetch();
    
    if (!$wallpaper) {
        return ['success' => false, 'message' => 'Wallpaper not found'];
    }
    
    // Initialize variables
    $file_path = $wallpaper['file_path'];
    $thumbnail_path = $wallpaper['thumbnail_path'];
    
    // Check if new image was uploaded
    if ($file && !empty($file['name'])) {
        $upload_result = uploadWallpaper($file);
        
        if ($upload_result['success']) {
            // Delete old files
            $old_image_path = UPLOAD_DIR . $wallpaper['file_path'];
            $old_thumb_path = THUMBNAIL_DIR . $wallpaper['thumbnail_path'];
            
            if (file_exists($old_image_path)) unlink($old_image_path);
            if (file_exists($old_thumb_path)) unlink($old_thumb_path);
            
            // Set new file paths
            $file_path = $upload_result['file_path'];
            $thumbnail_path = $upload_result['thumbnail_path'];
        } else {
            return $upload_result; // Return error message
        }
    }
    
    // Update wallpaper in database
    $stmt = $pdo->prepare("UPDATE wallpapers SET 
        title = ?, 
        description = ?, 
        category = ?, 
        file_path = ?, 
        thumbnail_path = ?, 
        is_featured = ?, 
        is_premium = ?, 
        updated_at = NOW() 
        WHERE id = ?");
    
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['category'],
        $file_path,
        $thumbnail_path,
        $data['is_featured'], 
        $data['is_premium'],
        $id
    ]);
    
    return [
        'success' => true, 
        'message' => 'Wallpaper updated successfully',
        'thumbnail_path' => $thumbnail_path
    ];
}

function validatePassword($password) {
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        return "Password must be at least " . MIN_PASSWORD_LENGTH . " characters long";
    }
    return true;
}

/**
 * Generates a complete URL for an image based on its path
 * @param string $image_path The relative path of the image
 * @return string The complete URL to the image
 */
function get_image_url($image_path) {
    global $site_url;
    
    // If the path is already a complete URL, return it as is
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        return $image_path;
    }
    
    // Remove any leading slashes to avoid double slashes
    $image_path = ltrim($image_path, '/');
    
    // Combine the site URL with the image path
    return rtrim($site_url, '/') . '/' . $image_path;
}
?>