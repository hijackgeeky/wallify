<?php
// Fetch categories from database
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h2 class="mb-4">Add New Wallpaper</h2>
    
    <div class="card">
        <div class="card-body">
            <!-- Notification area for AJAX responses -->
            <div id="notification-area"></div>
            
            <form method="POST" enctype="multipart/form-data" class="ajax-form"
                  action="process_ajax.php">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="add_wallpaper">
                
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="wallpaper" class="form-label">Wallpaper Image</label>
                    <input type="file" class="form-control image-upload" id="wallpaper" name="wallpaper" 
                           data-preview="#imagePreview" accept="image/*" required>
                    <div class="form-text">Max file size: 100MB. Allowed formats: JPG, JPEG, PNG, GIF.</div>
                    <img id="imagePreview" class="preview-image img-thumbnail mt-2" style="display: none;">
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured">
                    <label class="form-check-label" for="is_featured">Featured Wallpaper</label>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_premium" name="is_premium">
                    <label class="form-check-label" for="is_premium">Premium Wallpaper</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Wallpaper</button>
                <a href="admin.php?content=wallpapers" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
function submitWallpaperForm(form) {
    console.log('Direct form submission handler called');
    
    // Show loading spinner
    $('#loadingSpinner').show();
    
    // Create FormData object
    const formData = new FormData(form);
    
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
                // Clear the form
                $(form).trigger('reset');
                $('#imagePreview').hide();
                
                // Show success message with buttons
                const successAlert = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> ${response.message}
                        <div class="mt-2">
                            <a href="${response.redirect}" class="btn btn-primary btn-sm">View Wallpapers</a>
                            <button type="button" class="btn btn-secondary btn-sm ms-2" id="add-another">Add Another</button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                $('#notification-area').html(successAlert);
                
                // Add event handler for "Add Another" button
                $('#add-another').on('click', function() {
                    $('#notification-area').empty();
                });
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