<?php
/**
 * Router script for PHP built-in web server (php -S localhost:8000 router.php)
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$file = __DIR__ . $uri;

// If the file exists and is not a directory, serve it directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

require_once __DIR__ . '/index.php';
