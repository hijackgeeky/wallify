<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get categories for the navbar
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Get featured wallpapers
$stmt = $pdo->query("SELECT * FROM wallpapers WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 6");
$featured_wallpapers = $stmt->fetchAll();

// Get latest wallpapers
$stmt = $pdo->query("SELECT * FROM wallpapers ORDER BY created_at DESC LIMIT 12");
$latest_wallpapers = $stmt->fetchAll();

// Get premium wallpapers
$stmt = $pdo->query("SELECT * FROM wallpapers WHERE is_premium = 1 ORDER BY created_at DESC LIMIT 6");
$premium_wallpapers = $stmt->fetchAll();

// Get search query and category filter
$searchQuery = '';
if (isset($_GET['q'])) {
    if (is_array($_GET['q'])) {
        $searchQuery = '';
    } else {
        $searchQuery = trim($_GET['q']);
    }
}

$category = '';
if (isset($_GET['category'])) {
    if (is_array($_GET['category'])) {
        $category = '';
    } else {
        $category = trim($_GET['category']);
    }
}

// Log search parameters
error_log("Search query: " . $searchQuery);
error_log("Category filter: " . $category);

// Prepare the SQL query
$sql = "SELECT * FROM wallpapers WHERE 1=1";
$params = [];

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if (!empty($searchQuery)) {
    $sql .= " AND (LOWER(title) LIKE ? OR LOWER(description) LIKE ? OR LOWER(category) LIKE ?)";
    $searchTerm = '%' . strtolower($searchQuery) . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$sql .= " ORDER BY created_at DESC";

// Log the final SQL query
error_log("SQL Query: " . $sql);
error_log("Parameters: " . print_r($params, true));

// Prepare and execute the statement
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$wallpapers = $stmt->fetchAll();

// Log the number of results
error_log("Number of wallpapers found: " . count($wallpapers));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WalliFy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Navbar styling */
        .navbar {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 85%;
            max-width: 1200px;
            padding: 0.15rem 0.8rem;
            background-color: rgba(26, 26, 26, 0.85);
            backdrop-filter: blur(8px);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1030;
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: auto;
            min-height: 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar.navbar-hidden {
            transform: translate(-50%, -100%);
            transition: transform 0.3s ease;
        }

        .navbar-brand {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0;
            margin-right: 1rem;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .brand-logo {
            font-size: 0.95rem;
            color: #fff;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-link {
            padding: 0.15rem 0.6rem !important;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.9) !important;
            margin: 0 0.1rem;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.15);
        }

        .navbar-toggler {
            display: none;
            background: none;
            border: none;
            padding: 0.2rem;
            cursor: pointer;
            color: #fff;
        }

        .navbar-toggler:focus {
            outline: none;
        }

        /* Main content spacing */
        .container.mt-4 {
            margin-top: 60px !important;
            padding-top: 1.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .navbar {
                width: 90%;
                padding: 0.15rem 0.6rem;
            }

            .navbar-toggler {
                display: block;
            }

            .navbar-collapse {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: rgba(26, 26, 26, 0.95);
                backdrop-filter: blur(8px);
                border-radius: 6px;
                margin-top: 0.2rem;
                padding: 0.2rem;
                display: none;
            }

            .navbar-collapse.show {
                display: block;
            }

            .navbar-nav {
                flex-direction: column;
                align-items: stretch;
            }

            .nav-link {
                padding: 0.3rem 0.8rem !important;
                margin: 0.1rem 0;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                width: 92%;
            }
        }

        @media (max-width: 576px) {
            .navbar {
                width: 95%;
                top: 10px;
            }
        }

        /* Card styling with proper hover effects */
        .card {
            border-radius: 8px;
            overflow: hidden;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }

        .card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card-body {
            padding: 1rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .card-text {
            font-size: 0.9rem;
            color: #6c757d;
            line-height: 1.5;
        }
        
        .card-footer {
            background: transparent;
            border-top: 1px solid rgba(0,0,0,0.1);
            padding: 0.75rem;
            position: relative;
            z-index: 2;
        }
        
        .card-footer .d-flex {
            gap: 8px;
        }
        
        /* Button styling */
        .btn {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            font-size: 0.85rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            position: relative;
            z-index: 3;
            letter-spacing: 0.3px;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn i {
            margin-right: 4px;
            font-size: 0.9rem;
        }

        /* Badge styling */
        .badge {
            padding: 0.4em 0.6em;
            font-weight: 500;
            font-size: 0.75rem;
            border-radius: 4px;
            letter-spacing: 0.3px;
        }

        /* Footer styling */
        footer {
            background-color: #1a1a1a;
            color: #fff;
            padding: 3rem 0;
            margin-top: 4rem;
        }

        footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        footer a:hover {
            color: #fff;
        }

        footer h5 {
            margin-bottom: 1.2rem;
            font-weight: 600;
        }

        footer ul li {
            margin-bottom: 0.5rem;
        }

        /* Search form styling */
        .search-form {
            position: relative;
            max-width: 300px;
            margin: 0;
        }

        .search-form .form-control {
            height: 32px;
            padding: 0.2rem 0.5rem;
            font-size: 0.85rem;
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .search-form .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: none;
        }

        .search-form .btn {
            position: absolute;
            right: 0;
            top: 0;
            height: 32px;
            padding: 0 0.5rem;
            font-size: 0.85rem;
            border-radius: 0 6px 6px 0;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
        }

        .search-form .btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            margin-top: 0.2rem;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .suggestion-item {
            padding: 0.3rem 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .suggestion-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .suggestion-thumbnail {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 4px;
        }

        .suggestion-content {
            flex: 1;
            min-width: 0;
        }

        .suggestion-title {
            font-size: 0.85rem;
            color: #fff;
            margin-bottom: 0.1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .suggestion-category {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .search-loading {
            padding: 0.3rem 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }

        .search-loading .spinner-border {
            width: 0.8rem;
            height: 0.8rem;
            border-width: 0.15em;
        }
    </style>
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a class="dropdown-item" href="index.php?category=<?= urlencode($cat['name']) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
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
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <form class="search-form" id="searchForm">
                    <div class="position-relative">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search products, categories..." autocomplete="off">
                        <button type="submit" class="btn btn-outline-light">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions">
                        <!-- Search suggestions will be dynamically populated here -->
                        <div class="search-loading d-none">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>Searching...</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php if ($category || $searchQuery): ?>
            <!-- Filtered Results -->
            <h2 class="mb-4">
                <?php if ($category): ?>
                    Wallpapers in <?= htmlspecialchars($category) ?>
                <?php endif; ?>
                <?php if ($searchQuery): ?>
                    <?= $category ? ' matching "' : 'Search results for "' ?><?= htmlspecialchars($searchQuery) ?>"
                <?php endif; ?>
            </h2>
            
            <?php if (empty($wallpapers)): ?>
                <div class="alert alert-info">
                    No wallpapers found matching your search criteria.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($wallpapers as $wallpaper): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="<?= htmlspecialchars($wallpaper['thumbnail_path']) ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($wallpaper['title']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($wallpaper['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($wallpaper['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary"><?= htmlspecialchars($wallpaper['category']) ?></span>
                                        <?php if ($wallpaper['is_premium']): ?>
                                            <span class="badge bg-warning text-dark">Premium</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <a href="wallpaper.php?id=<?= $wallpaper['id'] ?>" class="btn btn-primary" onclick="window.location.href=this.href;">
                                            <i class="bi bi-eye-fill"></i>
                                            <span>View</span>
                                        </a>
                                        <a href="download.php?id=<?= $wallpaper['id'] ?>" class="btn btn-success" onclick="window.location.href=this.href;">
                                            <i class="bi bi-download"></i>
                                            <span>Download</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Featured Wallpapers -->
            <h2 class="mb-4">Featured Wallpapers</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
                <?php foreach ($featured_wallpapers as $wallpaper): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="uploads/thumbnails/<?= htmlspecialchars($wallpaper['thumbnail_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($wallpaper['title']) ?>">
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
                                <div class="d-flex justify-content-between">
                                    <a href="wallpaper.php?id=<?= $wallpaper['id'] ?>" class="btn btn-primary" onclick="window.location.href=this.href;">
                                        <i class="bi bi-eye-fill"></i>
                                        <span>View</span>
                                    </a>
                                    <a href="download.php?id=<?= $wallpaper['id'] ?>" class="btn btn-success" onclick="window.location.href=this.href;">
                                        <i class="bi bi-download"></i>
                                        <span>Download</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Latest Wallpapers -->
            <h2 class="mb-4">Latest Wallpapers</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
                <?php foreach ($latest_wallpapers as $wallpaper): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="uploads/thumbnails/<?= htmlspecialchars($wallpaper['thumbnail_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($wallpaper['title']) ?>">
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
                                <div class="d-flex justify-content-between">
                                    <a href="wallpaper.php?id=<?= $wallpaper['id'] ?>" class="btn btn-primary" onclick="window.location.href=this.href;">
                                        <i class="bi bi-eye-fill"></i>
                                        <span>View</span>
                                    </a>
                                    <a href="download.php?id=<?= $wallpaper['id'] ?>" class="btn btn-success" onclick="window.location.href=this.href;">
                                        <i class="bi bi-download"></i>
                                        <span>Download</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Premium Wallpapers -->
            <h2 class="mb-4">Premium Wallpapers</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($premium_wallpapers as $wallpaper): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="uploads/thumbnails/<?= htmlspecialchars($wallpaper['thumbnail_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($wallpaper['title']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($wallpaper['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(substr($wallpaper['description'], 0, 100)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?= htmlspecialchars($wallpaper['category']) ?></span>
                                    <span class="badge bg-warning text-dark">Premium</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <a href="wallpaper.php?id=<?= $wallpaper['id'] ?>" class="btn btn-primary" onclick="window.location.href=this.href;">
                                        <i class="bi bi-eye-fill"></i>
                                        <span>View</span>
                                    </a>
                                    <a href="download.php?id=<?= $wallpaper['id'] ?>" class="btn btn-success" onclick="window.location.href=this.href;">
                                        <i class="bi bi-download"></i>
                                        <span>Download</span>
                                    </a>
                                </div>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/search.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');
        let debounceTimer;

        // Handle form submission
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query) {
                window.location.href = `index.php?q=${encodeURIComponent(query)}`;
            }
        });

        // Function to fetch search suggestions
        const fetchSuggestions = async (query) => {
            if (query.length < 2) {
                searchSuggestions.style.display = 'none';
                return;
            }

            try {
                const response = await fetch(`search.php?q=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                
                if (!data.results || data.results.length === 0) {
                    searchSuggestions.innerHTML = `
                        <div class="p-3 text-center text-muted">
                            No results found
                        </div>
                    `;
                } else {
                    const suggestionsHtml = data.results.map(item => `
                        <div class="suggestion-item" data-id="${item.id}">
                            <img src="${item.thumbnail_path}" alt="${item.title}" class="suggestion-thumbnail">
                            <div class="suggestion-content">
                                <div class="suggestion-title">${item.title}</div>
                                <div class="suggestion-category">
                                    <span class="badge bg-primary">${item.category}</span>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    
                    searchSuggestions.innerHTML = suggestionsHtml;
                    
                    // Add click handlers to suggestions
                    document.querySelectorAll('.suggestion-item').forEach(item => {
                        item.addEventListener('click', () => {
                            window.location.href = `wallpaper.php?id=${item.dataset.id}`;
                        });
                    });
                }
                
                searchSuggestions.style.display = 'block';
                
            } catch (error) {
                console.error('Search error:', error);
                searchSuggestions.innerHTML = `
                    <div class="p-3 text-center text-danger">
                        Error loading suggestions
                    </div>
                `;
            }
        };

        // Debounce search input
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const query = searchInput.value.trim();
                fetchSuggestions(query);
            }, 300);
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchForm.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });

        // Handle keyboard navigation
        searchInput.addEventListener('keydown', (e) => {
            const items = searchSuggestions.querySelectorAll('.suggestion-item');
            const current = searchSuggestions.querySelector('.suggestion-item.active');
            
            if (items.length === 0) return;
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (!current) {
                        items[0].classList.add('active');
                    } else {
                        const next = [...items].indexOf(current) + 1;
                        if (next < items.length) {
                            current.classList.remove('active');
                            items[next].classList.add('active');
                        }
                    }
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    if (!current) {
                        items[items.length - 1].classList.add('active');
                    } else {
                        const prev = [...items].indexOf(current) - 1;
                        if (prev >= 0) {
                            current.classList.remove('active');
                            items[prev].classList.add('active');
                        }
                    }
                    break;
                    
                case 'Enter':
                    if (current) {
                        e.preventDefault();
                        window.location.href = `wallpaper.php?id=${current.dataset.id}`;
                    }
                    break;
                    
                case 'Escape':
                    searchSuggestions.style.display = 'none';
                    searchInput.blur();
                    break;
            }
        });
    });
    </script>
    <script>
        // Navbar hide on scroll
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down & past threshold
                navbar.classList.add('navbar-hidden');
            } else {
                // Scrolling up or at top
                navbar.classList.remove('navbar-hidden');
            }
            
            lastScrollTop = scrollTop;
        });
    </script>
</body>
</html>