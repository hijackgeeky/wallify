<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get categories for the navbar
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - WalliFy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
                        <a class="nav-link" href="featured.php">Featured</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About Us</a>
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
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <h1 class="mb-4 text-center">About Us</h1>
                
                <div class="mission-section">
                    <h3>Our Story</h3>
                    <p>
                        Wallpaper Gallery was founded with a simple mission: to provide high-quality wallpapers for all devices. 
                        What started as a small collection has grown into a comprehensive gallery featuring thousands of carefully 
                        curated wallpapers across various categories.
                    </p>
                    <p>
                        Our journey began when a group of design enthusiasts noticed the lack of quality wallpapers available online. 
                        We decided to create a platform that not only offers beautiful wallpapers but also ensures they are optimized 
                        for different devices and screen sizes.
                    </p>
                </div>
                
                <div class="mission-section">
                    <h3>Our Mission</h3>
                    <p>
                        At Wallpaper Gallery, our mission is to enhance the visual experience of our users by providing them with 
                        stunning wallpapers that reflect their personality and style. We believe that the right wallpaper can 
                        transform a device and make it feel more personal and engaging.
                    </p>
                    <p>
                        We are committed to maintaining the highest standards of quality and continuously expanding our collection 
                        to include the latest trends and timeless classics. Our premium and featured sections showcase our best 
                        wallpapers, carefully selected to meet the diverse tastes of our users.
                    </p>
                </div>
                
                <h3 class="text-center mb-4">Meet Our Team</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="team-member">
                            <img src="assets/images/team/anuj.jpg" alt="Anuj Kumar" class="img-fluid">
                            <h4>Anuj Kumar</h4>
                            <p>Backend Developer</p>
                            <div class="social-links">
                                <a href="#"><i class="bi bi-github"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="team-member">
                            <img src="assets/images/team/vani.jpg" alt="Vani Chandran" class="img-fluid">
                            <h4>Vani Chandran</h4>
                            <p>Frontend Developer</p>
                            <div class="social-links">
                                <a href="#"><i class="bi bi-github"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                                <a href="#"><i class="bi bi-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mission-section mt-5">
                    <h3>Our Values</h3>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="bi bi-star-fill fs-1 text-primary mb-3"></i>
                                <h5>Quality</h5>
                                <p>We never compromise on the quality of our wallpapers, ensuring they meet the highest standards.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="bi bi-people-fill fs-1 text-primary mb-3"></i>
                                <h5>Community</h5>
                                <p>We value our community of users and strive to create a platform that serves their needs.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="bi bi-lightning-fill fs-1 text-primary mb-3"></i>
                                <h5>Innovation</h5>
                                <p>We continuously innovate to provide new features and improve the user experience.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="premium.php">Premium</a></li>
                        <li><a href="featured.php">Featured</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Categories</h5>
                    <ul class="list-unstyled">
                        <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li><a href="index.php?category=<?= urlencode($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></a></li>
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