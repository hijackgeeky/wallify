<?php
// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin.php?content=admins');
    exit;
}

$id = (int)$_GET['id'];

// Get admin details
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$id]);
$admin = $stmt->fetch();

// If admin not found, redirect back
if (!$admin) {
    $_SESSION['flash_message'] = 'Admin not found';
    $_SESSION['flash_type'] = 'danger';
    header('Location: admin.php?content=admins');
    exit;
}

// Don't allow editing super admin (ID 1)
if ($id === 1 && $_SESSION['admin_id'] !== 1) {
    $_SESSION['flash_message'] = 'You cannot edit the super admin account';
    $_SESSION['flash_type'] = 'danger';
    header('Location: admin.php?content=admins');
    exit;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Admin</h2>
        <a href="admin.php?content=admins" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Admins
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="process_ajax.php" method="post" class="ajax-form">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                <input type="hidden" name="action" value="update_admin">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                    <div class="form-text">Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.</div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</div> 