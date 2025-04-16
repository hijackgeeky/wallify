<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get categories for the navbar
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Get featured wallpapers
$stmt = $pdo->query("SELECT * FROM wallpapers WHERE is_featured = 1 ORDER BY created_at DESC");
$featured_wallpapers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured Wallpapers - WalliFy</title>
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
                                <li>
                                    <a class="dropdown-item" href="index.php?category=<?= urlencode($category['name']) ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="premium.php">Premium</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="featured.php">Featured</a>
                    </li>
                </ul>
                <form class="search-form" action="index.php" method="GET">
                    <input class="form-control" type="search" name="search" placeholder="Search wallpapers..." 
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <h2 class="mb-4">Featured Wallpapers</h2>
        
        <?php if (empty($featured_wallpapers)): ?>
            <div class="alert alert-info">No featured wallpapers available at the moment.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($featured_wallpapers as $wallpaper): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="uploads/thumbnails/<?= $wallpaper['thumbnail_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($wallpaper['title']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($wallpaper['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(substr($wallpaper['description'], 0, 100)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?= htmlspecialchars($wallpaper['category']) ?></span>
                                    <?php if ($wallpaper['is_premium']): ?>
                                        <span class="badge bg-warning text-dark">Premium</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="wallpaper.php?id=<?= $wallpaper['id'] ?>" class="btn btn-primary btn-sm">View</a>
                                <a href="download.php?id=<?= $wallpaper['id'] ?>" class="btn btn-success btn-sm">Download</a>
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
                    <h5>WalliFy</h5>
                    <p>Find the perfect wallpaper for your device.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="premium.php" class="text-white">Premium</a></li>
                        <li><a href="featured.php" class="text-white">Featured</a></li>
                        <li><a href="about.php" class="text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
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
                <p>&copy; <?= date('Y') ?> WalliFy. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 