<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');
$root = dirname(__DIR__);
echo "PHP " . PHP_VERSION . "\n";
foreach (['pdo','pdo_pgsql','pdo_sqlite','mbstring','curl'] as $e)
    echo "$e: " . (extension_loaded($e) ? 'OK' : 'ABSENT') . "\n";
echo "SUPABASE_DB_URL: " . (getenv('SUPABASE_DB_URL') ? 'défini' : 'ABSENT') . "\n";
echo "Fichiers: " . implode(', ', scandir($root)) . "\n\n";
chdir($root);
foreach (['models/SessionManager.php','db.php','models/i18n.php','fonctions.php','models/classes.php'] as $f) {
    echo "require $f ... ";
    try { require_once $f; echo "OK\n"; }
    catch (Throwable $e) { echo "FAIL: {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}\n"; }
}