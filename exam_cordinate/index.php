<?php
// Detect current script's directory
$baseDir = dirname(__FILE__);

// Redirect everything to public/index.php
$uri = trim($_SERVER['REQUEST_URI'], '/');

// Check if the request is for an actual file or directory
if ($uri && file_exists($baseDir . '/public/' . $uri)) {
    return false; // Serve the requested resource directly (CSS, JS, images, etc.)
} else {
    // Redirect all other requests to public/index.php
    require_once $baseDir . '/public/index.php';
}
