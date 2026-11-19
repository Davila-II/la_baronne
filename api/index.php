<?php
/**
 * Vercel Serverless Function entry point
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('HTTP/1.1 500 Internal Server Error');
        echo "<h1>Fatal Error</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . " (line " . $error['line'] . ")</p>";
    }
});

// Resolve the project root directory
$rootDir = dirname(__DIR__);
if (file_exists($rootDir . '/index.php')) {
    chdir($rootDir);
    require $rootDir . '/index.php';
} elseif (file_exists(__DIR__ . '/index.php')) {
    require __DIR__ . '/index.php';
} else {
    echo "<h1>Error: Cannot locate index.php</h1>";
    echo "<p>Current dir: " . htmlspecialchars(__DIR__) . "</p>";
    echo "<p>Files in current dir: " . implode(', ', scandir(__DIR__)) . "</p>";
    echo "<p>Files in parent dir: " . implode(', ', scandir(dirname(__DIR__))) . "</p>";
}



