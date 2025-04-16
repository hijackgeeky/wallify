<div class="container-fluid">
    <h2 class="mb-4">Settings</h2>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" class="ajax-form" action="process_ajax.php?action=update_settings">
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="mb-3">
                    <label for="site_name" class="form-label">Site Name</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="Wallpaper Gallery">
                </div>
                
                <div class="mb-3">
                    <label for="site_description" class="form-label">Site Description</label>
                    <textarea class="form-control" id="site_description" name="site_description" rows="3">High quality wallpapers collection</textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Maintenance Mode</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode">
                        <label class="form-check-label" for="maintenance_mode">Enable maintenance mode</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>