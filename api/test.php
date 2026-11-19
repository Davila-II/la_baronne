<?php
header('Content-Type: text/plain');
echo "PHP is working on Vercel!\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Extensions: " . implode(', ', get_loaded_extensions()) . "\n";
