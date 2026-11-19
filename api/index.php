<?php
/**
 * Vercel Serverless Function entry point
 */
// Set document root / directory context
chdir(__DIR__ . '/..');

// Require the main application router/entry point
require __DIR__ . '/../index.php';
