<div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width: 280px; min-height: 100vh;">
    <a href="admin.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Wallpaper Admin</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="admin.php?content=dashboard" class="nav-link <?= $content === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php?content=wallpapers" class="nav-link <?= $content === 'wallpapers' ? 'active' : '' ?>">
                <i class="bi bi-images me-2"></i>
                Wallpapers
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php?content=add_wallpaper" class="nav-link <?= $content === 'add_wallpaper' ? 'active' : '' ?>">
                <i class="bi bi-plus-circle me-2"></i>
                Add Wallpaper
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php?content=edit_wallpaper" class="nav-link <?= $content === 'edit_wallpaper' ? 'active' : '' ?>">
                <i class="bi bi-pencil-square me-2"></i>
                Edit Wallpaper
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php?content=categories" class="nav-link <?= $content === 'categories' ? 'active' : '' ?>">
                <i class="bi bi-tags me-2"></i>
                Categories
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php?content=admins" class="nav-link <?= $content === 'admins' ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i>
                Admins
            </a>
        </li>
        <li class="nav-item">
            <a href="admin.php?content=settings" class="nav-link <?= $content === 'settings' ? 'active' : '' ?>">
                <i class="bi bi-gear me-2"></i>
                Settings
            </a>
        </li>
    </ul>
    <hr>
    <div class="dropdown mt-auto">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-4 me-2"></i>
            <strong><?= htmlspecialchars($current_admin['username']) ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><a class="dropdown-item" href="#">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
        </ul>
    </div>
</div>