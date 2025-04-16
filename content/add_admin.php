<div class="container-fluid">
    <h2 class="mb-4">Add New Admin</h2>
    
    <!-- Notification area for AJAX responses -->
    <div id="notification-area"></div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" class="ajax-form" action="process_ajax.php?action=add_admin">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">Minimum 8 characters</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Admin</button>
                <a href="admin.php?content=admins" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>