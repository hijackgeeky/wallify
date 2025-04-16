<?php
// Fetch categories from database
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Check for wallpaper ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // No ID provided, show a list of wallpapers to edit
    
    // Handle filtering
    $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
    $where = '';
    $params = [];

    switch ($filter) {
        case 'featured':
            $where = 'WHERE w.is_featured = 1 AND w.is_premium = 0';
            break;
        case 'premium':
            $where = 'WHERE w.is_premium = 1 AND w.is_featured = 0';
            break;
        case 'both':
            $where = 'WHERE w.is_featured = 1 AND w.is_premium = 1';
            break;
    }

    // Handle search
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = sanitize($_GET['search']);
        $where = $where ? $where . ' AND ' : 'WHERE ';
        $where .= "(title LIKE ? OR description LIKE ? OR category LIKE ?)";
        $params = array_fill(0, 3, "%$search%");
    }
    
    // Get all wallpapers with filters
    $query = "SELECT w.*, a.username as creator FROM wallpapers w LEFT JOIN admins a ON w.created_by = a.id $where ORDER BY w.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $wallpapers = $stmt->fetchAll();
    ?>
    
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Wallpaper</h2>
            <a href="admin.php?content=add_wallpaper" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="content" value="edit_wallpaper">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search wallpapers..." 
                               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="filter" class="form-select">
                            <option value="">All Wallpapers</option>
                            <option value="featured" <?= ($filter === 'featured') ? 'selected' : '' ?>>Featured</option>
                            <option value="premium" <?= ($filter === 'premium') ? 'selected' : '' ?>>Premium</option>
                            <option value="both" <?= ($filter === 'both') ? 'selected' : '' ?>>Featured & Premium</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($wallpapers)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No wallpapers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($wallpapers as $wallpaper): ?>
                                    <tr>
                                        <td><?= $wallpaper['id'] ?></td>
                                        <td>
                                            <img src="uploads/thumbnails/<?= $wallpaper['thumbnail_path'] ?>" 
                                                 alt="Thumbnail" width="60" class="img-thumbnail">
                                        </td>
                                        <td><?= htmlspecialchars($wallpaper['title']) ?></td>
                                        <td><?= htmlspecialchars($wallpaper['category']) ?></td>
                                        <td>
                                            <?php if ($wallpaper['is_featured']): ?>
                                                <span class="badge bg-success">Featured</span>
                                            <?php endif; ?>
                                            <?php if ($wallpaper['is_premium']): ?>
                                                <span class="badge bg-warning text-dark">Premium</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($wallpaper['creator']) ?></td>
                                        <td><?= date('M d, Y', strtotime($wallpaper['created_at'])) ?></td>
                                        <td>
                                            <a href="admin.php?content=edit_wallpaper&id=<?= $wallpaper['id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    exit;
}

$id = (int)$_GET['id'];

// Get wallpaper details
$stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE id = ?");
$stmt->execute([$id]);
$wallpaper = $stmt->fetch();

if (!$wallpaper) {
    echo '<div class="alert alert-danger">Wallpaper not found</div>';
    exit;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Wallpaper</h2>
        <a href="admin.php?content=edit_wallpaper" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <!-- Notification area for AJAX responses -->
            <div id="notification-area"></div>
            
            <form method="POST" enctype="multipart/form-data" class="ajax-form" action="process_ajax.php">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="update_wallpaper">
                <input type="hidden" name="id" value="<?= $wallpaper['id'] ?>">
                
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($wallpaper['title']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($wallpaper['description']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" 
                                    <?= ($wallpaper['category'] === $cat['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Current Wallpaper</label>
                    <div class="mb-3">
                        <img src="uploads/thumbnails/<?= $wallpaper['thumbnail_path'] ?>" alt="Current Thumbnail" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                    
                    <label for="wallpaper" class="form-label">New Wallpaper Image (optional)</label>
                    <input type="file" class="form-control image-upload" id="wallpaper" name="wallpaper" data-preview="#imagePreview" accept="image/*">
                    <div class="form-text">Leave empty to keep current image. Max file size: 100MB. Allowed formats: JPG, JPEG, PNG, GIF.</div>
                    <img id="imagePreview" class="preview-image img-thumbnail mt-2" style="display: none;">
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" <?= $wallpaper['is_featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_featured">Featured Wallpaper</label>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_premium" name="is_premium" <?= $wallpaper['is_premium'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_premium">Premium Wallpaper</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Wallpaper</button>
            </form>
        </div>
    </div>
</div>

<script>
function submitWallpaperForm(form) {
    console.log('Edit wallpaper form submission handler called');
    
    // Show loading spinner
    $('#loadingSpinner').show();
    
    // Create FormData object
    const formData = new FormData(form);
    
    // Log form data for debugging
    console.log('Featured checkbox state:', $('#is_featured').is(':checked'));
    console.log('Premium checkbox state:', $('#is_premium').is(':checked'));
    
    // Explicitly set checkbox values (unchecked checkboxes don't get submitted)
    formData.set('is_featured', $('#is_featured').is(':checked') ? '1' : '0');
    formData.set('is_premium', $('#is_premium').is(':checked') ? '1' : '0');
    
    // Send AJAX request
    $.ajax({
        url: 'process_ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log('AJAX success response:', response);
            $('#loadingSpinner').hide();
            
            if (response.success) {
                // Show success message with buttons
                const successAlert = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> ${response.message}
                        <div class="mt-2">
                            <a href="${response.redirect}" class="btn btn-primary btn-sm">View Wallpapers</a>
                            <button type="button" class="btn btn-secondary btn-sm ms-2" id="continue-editing">Continue Editing</button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                $('#notification-area').html(successAlert);
                
                // Add event handler for "Continue Editing" button
                $('#continue-editing').on('click', function() {
                    $('#notification-area').empty();
                });
                
                // Update preview if new image was uploaded
                if (response.thumbnail_path) {
                    $('img[alt="Current Thumbnail"]').attr('src', 'uploads/thumbnails/' + response.thumbnail_path + '?' + new Date().getTime());
                }
            } else {
                // Show error message
                const errorAlert = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${response.message || 'An error occurred'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                $('#notification-area').html(errorAlert);
            }
        },
        error: function(xhr, status, error) {
            $('#loadingSpinner').hide();
            console.error('AJAX error:', status, error);
            console.log('Response text:', xhr.responseText);
            
            // Show error message
            const errorAlert = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Request failed: ${error}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#notification-area').html(errorAlert);
        }
    });
    
    // Prevent normal form submission
    return false;
}
</script>
