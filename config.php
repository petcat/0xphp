<?php
// config.php - Configuration file

// Database configuration for SQLite
define('DB_PATH', 'data/products.db');

// Other configurations
define('SITE_NAME', 'Product Showcase');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@example.com');

// Security settings
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

?>
