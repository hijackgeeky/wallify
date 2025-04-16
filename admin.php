<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Default content to load
$content = 'dashboard';
$allowed_content = [
    'dashboard' => 'Dashboard',
    'wallpapers' => 'Wallpapers',
    'add_wallpaper' => 'Add Wallpaper',
    'edit_wallpaper' => 'Edit Wallpaper',
    'categories' => 'Categories',
    'admins' => 'Admin Users',
    'add_admin' => 'Add Admin',
    'edit_admin' => 'Edit Admin',
    'settings' => 'Settings'
];

// Get requested content from URL
if (isset($_GET['content']) && array_key_exists($_GET['content'], $allowed_content)) {
    $content = $_GET['content'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= $allowed_content[$content] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        #sidebar {
            min-height: 100vh;
            background: #212529;
            transition: all 0.3s;
            width: 280px;
        }
        #sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 5px;
            margin-bottom: 5px;
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            background: #0d6efd;
            color: white;
        }
        #sidebar .nav-link i {
            margin-right: 10px;
        }
        #content-area {
            padding: 20px;
            min-height: 100vh;
            width: calc(100% - 280px);
        }
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/navbar.php'; ?>

        <!-- Main Content Area -->
        <div id="content-area">
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] === 'success' ? 'success' : ($_SESSION['flash_type'] === 'danger' ? 'danger' : 'info') ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
                ?>
            <?php endif; ?>
            <?php include 'content/' . $content . '.php'; ?>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
    </div>

    <!-- Modal for dynamic content -->
    <div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>