<?php
// Fetch all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<div class="container-fluid">
    <!-- Notification area for AJAX responses -->
    <div id="notification-area"></div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add New Category</h5>
                </div>
                <div class="card-body">
                    <form id="addCategoryForm" onsubmit="return submitCategoryForm(event)">
                        <input type="hidden" name="action" value="add_category">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Existing Categories</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Display Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category['name']) ?></td>
                                        <td><?= htmlspecialchars($category['display_order']) ?></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-category" 
                                                    data-id="<?= $category['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($category['name']) ?>"
                                                    data-display-order="<?= $category['display_order'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="post" action="process_ajax.php" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger delete-category" 
                                                        onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm" onsubmit="return submitEditCategoryForm(event)">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" id="edit_category_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_display_order" class="form-label">Display Order</label>
                        <input type="number" class="form-control" id="edit_display_order" name="display_order" value="0" min="0">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function submitCategoryForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
    
    fetch('process_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.status === 'success') {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.getElementById('notification-area').appendChild(alert);
            
            // Clear the form
            form.reset();
            
            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message || 'An error occurred'}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.getElementById('notification-area').appendChild(alert);
            
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Show error message
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            An error occurred while adding the category
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.getElementById('notification-area').appendChild(alert);
        
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
    
    return false;
}

function submitEditCategoryForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
    
    fetch('process_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.status === 'success') {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.getElementById('notification-area').appendChild(alert);
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
            
            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message || 'An error occurred'}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.getElementById('notification-area').appendChild(alert);
            
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Show error message
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            An error occurred while updating the category
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.getElementById('notification-area').appendChild(alert);
        
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
    
    return false;
}

// Handle edit button clicks
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const displayOrder = this.dataset.displayOrder;
            
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_display_order').value = displayOrder;
            
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        });
    });
});
</script> 