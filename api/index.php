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

// Set document root / directory context
chdir(__DIR__ . '/..');

try {
    // Require the main application router/entry point
    require __DIR__ . '/../index.php';
} catch (Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>Application Error (500)</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (line " . $e->getLine() . ")</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}


