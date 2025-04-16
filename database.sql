-- Create database if not exists
CREATE DATABASE IF NOT EXISTS wallpaper_admin;
USE wallpaper_admin;

-- Create wallpapers table if not exists
CREATE TABLE IF NOT EXISTS wallpapers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_premium TINYINT(1) NOT NULL DEFAULT 0,
    download_count INT NOT NULL DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_featured (is_featured),
    INDEX idx_premium (is_premium)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create categories table if not exists
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample categories if not exists
INSERT IGNORE INTO categories (name, description) VALUES
('Nature', 'Beautiful nature wallpapers'),
('Abstract', 'Abstract and artistic wallpapers'),
('Animals', 'Animal themed wallpapers'),
('Space', 'Space and astronomy wallpapers'),
('Technology', 'Technology and gadget wallpapers');

-- Insert sample wallpapers if not exists
INSERT IGNORE INTO wallpapers (title, description, category, file_path, thumbnail_path, is_featured, is_premium) VALUES
('Mountain Sunset', 'Beautiful mountain landscape at sunset', 'Nature', 'mountain_sunset.jpg', 'thumb_mountain_sunset.jpg', 1, 0),
('Abstract Waves', 'Colorful abstract wave pattern', 'Abstract', 'abstract_waves.jpg', 'thumb_abstract_waves.jpg', 1, 1),
('Lion King', 'Majestic lion in the savanna', 'Animals', 'lion_king.jpg', 'thumb_lion_king.jpg', 1, 0),
('Galaxy Nebula', 'Colorful galaxy nebula in space', 'Space', 'galaxy_nebula.jpg', 'thumb_galaxy_nebula.jpg', 1, 1),
('Tech Setup', 'Modern technology workspace', 'Technology', 'tech_setup.jpg', 'thumb_tech_setup.jpg', 1, 0); 