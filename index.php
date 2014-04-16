<?php
if (!file_exists(__DIR__ . '/app/open/config.php')) {
    strpos($_SERVER['REQUEST_URI'], 'check.php') ? die('Script "check.php" does not found.') : header('Location: web/check.php');
    exit;
}

require_once __DIR__ . '/app/bootstrap.php';
$app = new ElfChat\Application();
$app->run();