<?php
// Handle filtering
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where = '';
$params = [];

switch ($filter) {
    case 'featured':
        $where = 'WHERE is_featured = 1';
        break;
    case 'premium':
        $where = 'WHERE is_premium = 1';
        break;
}

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $where = $where ? $where . ' AND ' : 'WHERE ';
    $where .= "(title LIKE ? OR description LIKE ? OR category LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}

// Get wallpapers
$query = "SELECT w.*, a.username as creator FROM wallpapers w LEFT JOIN admins a ON w.created_by = a.id $where ORDER BY w.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$wallpapers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?> alert-dismissible fade show mb-4" role="alert">
            <?= $_SESSION['flash_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        // Clear the flash message after displaying it
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Wallpapers</h2>
        <a href="admin.php?content=add_wallpaper" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="content" value="wallpapers">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search wallpapers..." 
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <select name="filter" class="form-select">
                        <option value="">All Wallpapers</option>
                        <option value="featured" <?= ($filter === 'featured') ? 'selected' : '' ?>>Featured</option>
                        <option value="premium" <?= ($filter === 'premium') ? 'selected' : '' ?>>Premium</option>
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
                <table id="wallpapers-table" class="table table-striped datatable">
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
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="process_ajax.php?action=delete_wallpaper&id=<?= $wallpaper['id'] ?>&_token=<?= $_SESSION['csrf_token'] ?>" 
                                           class="btn btn-sm btn-outline-danger confirm-delete">
                                            <i class="bi bi-trash"></i>
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