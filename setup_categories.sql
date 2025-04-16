-- Create categories table if it doesn't exist
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial categories if they don't exist
INSERT IGNORE INTO `categories` (`name`, `description`) VALUES
('Nature', 'Beautiful landscapes, mountains, forests, and natural scenery'),
('Animals', 'Wildlife, pets, and animal photography'),
('Cities', 'Urban landscapes, architecture, and city life'),
('Abstract', 'Abstract art and patterns'),
('Space', 'Astronomy, galaxies, and cosmic imagery'),
('Cars', 'Automobiles and vehicles'),
('Food', 'Food photography and culinary arts'),
('Sports', 'Sports action and athletic imagery'); 