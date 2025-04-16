<?php
// Get all admins
$admins = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC")->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Admins</h2>
        <a href="admin.php?content=add_admin" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="admins-table" class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No admins found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= $admin['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($admin['username']) ?>
                                        <?php if ($admin['id'] === 1): ?>
                                            <span class="badge bg-danger">Super Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($admin['email']) ?></td>
                                    <td><?= date('M d, Y', strtotime($admin['created_at'])) ?></td>
                                    <td><?= $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never' ?></td>
                                    <td>
                                        <a href="admin.php?content=edit_admin&id=<?= $admin['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($admin['id'] !== 1 && $admin['id'] !== $_SESSION['admin_id']): ?>
                                            <a href="process_ajax.php?action=delete_admin&id=<?= $admin['id'] ?>&_token=<?= $_SESSION['csrf_token'] ?>" 
                                               class="btn btn-sm btn-outline-danger confirm-delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
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