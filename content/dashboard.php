<?php
// Get counts for dashboard
$wallpapers_count = $pdo->query("SELECT COUNT(*) FROM wallpapers")->fetchColumn();
$admins_count = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
$featured_count = $pdo->query("SELECT COUNT(*) FROM wallpapers WHERE is_featured = 1")->fetchColumn();
$premium_count = $pdo->query("SELECT COUNT(*) FROM wallpapers WHERE is_premium = 1")->fetchColumn();

// Get recent wallpapers
$recent_wallpapers = $pdo->query(
    "SELECT w.*, a.username as creator 
     FROM wallpapers w 
     LEFT JOIN admins a ON w.created_by = a.id 
     ORDER BY w.created_at DESC LIMIT 5"
)->fetchAll();

// Get recent activity
$recent_activity = $pdo->query(
    "SELECT a.username, w.title, w.created_at 
     FROM wallpapers w 
     JOIN admins a ON w.created_by = a.id 
     ORDER BY w.created_at DESC LIMIT 5"
)->fetchAll();
?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard</h2>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Wallpapers</h5>
                    <h2><?= number_format($wallpapers_count) ?></h2>
                    <a href="admin.php?content=wallpapers" class="text-white">View all</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Featured</h5>
                    <h2><?= number_format($featured_count) ?></h2>
                    <a href="admin.php?content=wallpapers&filter=featured" class="text-white">View featured</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Premium</h5>
                    <h2><?= number_format($premium_count) ?></h2>
                    <a href="admin.php?content=wallpapers&filter=premium" class="text-white">View premium</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Admins</h5>
                    <h2><?= number_format($admins_count) ?></h2>
                    <a href="admin.php?content=admins" class="text-white">View admins</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Wallpapers</span>
                    <a href="admin.php?content=wallpapers" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if ($recent_wallpapers): ?>
                        <div class="list-group">
                            <?php foreach ($recent_wallpapers as $wallpaper): ?>
                                <a href="admin.php?content=edit_wallpaper&id=<?= $wallpaper['id'] ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($wallpaper['title']) ?></h6>
                                            <small class="text-muted">By <?= htmlspecialchars($wallpaper['creator']) ?></small>
                                        </div>
                                        <small><?= date('M d, Y', strtotime($wallpaper['created_at'])) ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">No wallpapers found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    Recent Activity
                </div>
                <div class="card-body">
                    <?php if ($recent_activity): ?>
                        <ul class="list-group">
                            <?php foreach ($recent_activity as $activity): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?= htmlspecialchars($activity['username']) ?></strong> added 
                                            <em>"<?= htmlspecialchars($activity['title']) ?>"</em>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('M d, H:i', strtotime($activity['created_at'])) ?>
                                        </small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">No recent activity</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>