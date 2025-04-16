<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Get wallpaper details
$stmt = $pdo->prepare("SELECT w.*, a.username as creator FROM wallpapers w LEFT JOIN admins a ON w.created_by = a.id WHERE w.id = ?");
$stmt->execute([$id]);
$wallpaper = $stmt->fetch();

// If wallpaper not found, redirect to home
if (!$wallpaper) {
    header('Location: index.php');
    exit;
}

// Get related wallpapers from the same category
$stmt = $pdo->prepare("SELECT * FROM wallpapers WHERE category = ? AND id != ? ORDER BY created_at DESC LIMIT 6");
$stmt->execute([$wallpaper['category'], $id]);
$related_wallpapers = $stmt->fetchAll();

// Get categories for the navbar
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($wallpaper['title']) ?> - Wallpaper Gallery</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="brand-container">
                    <i class="bi bi-image brand-logo"></i>
                    <span class="brand-name">WalliFy</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <li><a class="dropdown-item" href="index.php?category=<?= urlencode($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="premium.php">Premium</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="featured.php">Featured</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <form class="d-flex search-form" action="index.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search wallpapers..." 
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php?category=<?= urlencode($wallpaper['category']) ?>"><?= htmlspecialchars($wallpaper['category']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($wallpaper['title']) ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-8">
                <?php if (isset($_GET['error']) && $_GET['error'] == 'file_not_found'): ?>
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i> The wallpaper file could not be found. Please contact the administrator.
                </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <img src="uploads/<?= $wallpaper['file_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($wallpaper['title']) ?>">
                    <div class="card-body">
                        <h1 class="card-title"><?= htmlspecialchars($wallpaper['title']) ?></h1>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary"><?= htmlspecialchars($wallpaper['category']) ?></span>
                            <?php if ($wallpaper['is_premium']): ?>
                                <span class="badge bg-warning text-dark">Premium</span>
                            <?php endif; ?>
                            <?php if ($wallpaper['is_featured']): ?>
                                <span class="badge bg-success">Featured</span>
                            <?php endif; ?>
                        </div>
                        <p class="card-text"><?= nl2br(htmlspecialchars($wallpaper['description'])) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Added by <?= htmlspecialchars($wallpaper['creator']) ?> on <?= date('F j, Y', strtotime($wallpaper['created_at'])) ?></small>
                            <div>
                                <a href="download.php?id=<?= $wallpaper['id'] ?>" class="btn btn-success" id="downloadBtn">Download</a>
                                <button class="btn btn-primary" id="setWallpaperBtn">Set as Wallpaper</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Wallpaper Details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Category</span>
                                <a href="index.php?category=<?= urlencode($wallpaper['category']) ?>" class="text-decoration-none"><?= htmlspecialchars($wallpaper['category']) ?></a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Added By</span>
                                <span><?= htmlspecialchars($wallpaper['creator']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Date Added</span>
                                <span><?= date('F j, Y', strtotime($wallpaper['created_at'])) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Type</span>
                                <span>
                                    <?php if ($wallpaper['is_premium']): ?>
                                        <span class="badge bg-warning text-dark">Premium</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Standard</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Share</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" id="copyLinkBtn">
                                <i class="bi bi-link-45deg"></i> Copy Link
                            </button>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-facebook"></i> Share on Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode('Check out this wallpaper: ' . $wallpaper['title']) ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-twitter"></i> Share on Twitter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($related_wallpapers)): ?>
            <h3 class="mt-5 mb-4">Related Wallpapers</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($related_wallpapers as $related): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="uploads/thumbnails/<?= $related['thumbnail_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($related['title']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($related['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(substr($related['description'], 0, 100)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?= htmlspecialchars($related['category']) ?></span>
                                    <?php if ($related['is_premium']): ?>
                                        <span class="badge bg-warning text-dark">Premium</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="wallpaper.php?id=<?= $related['id'] ?>" class="btn btn-primary btn-sm">View</a>
                                <a href="download.php?id=<?= $related['id'] ?>" class="btn btn-success btn-sm">Download</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Wallpaper Gallery</h5>
                    <p>Find the perfect wallpaper for your device.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="index.php?premium=1" class="text-white">Premium</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Categories</h5>
                    <ul class="list-unstyled">
                        <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li><a href="index.php?category=<?= urlencode($category['name']) ?>" class="text-white"><?= htmlspecialchars($category['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Wallpaper Gallery. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script>
        // Copy link functionality
        document.getElementById('copyLinkBtn').addEventListener('click', function() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('Link copied to clipboard!');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        });

        // Download button functionality
        document.getElementById('downloadBtn').addEventListener('click', function(e) {
            // Add loading state
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Downloading...';
            
            // The page will navigate to download.php which will trigger the download
            // No need to prevent default
        });

        // Set as wallpaper functionality
        document.getElementById('setWallpaperBtn').addEventListener('click', function() {
            const wallpaperUrl = '<?= 'http://' . $_SERVER['HTTP_HOST'] . '/uploads/' . $wallpaper['file_path'] ?>';
            
            // Check if the browser supports the Wallpaper API
            if ('setWallpaper' in navigator) {
                navigator.setWallpaper({
                    url: wallpaperUrl
                }).then(() => {
                    alert('Wallpaper set successfully!');
                }).catch(err => {
                    console.error('Error setting wallpaper:', err);
                    alert('Failed to set wallpaper. Please download and set it manually.');
                });
            } else {
                alert('Your browser does not support setting wallpapers directly. Please download and set it manually.');
            }
        });
    </script>
</body>
</html> 