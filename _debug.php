<?php
require_once __DIR__ . '/config/app.php';
echo '<pre>';
echo 'APP_BASE    = [' . APP_BASE . ']' . PHP_EOL;
echo 'HTTP_HOST   = [' . ($_SERVER['HTTP_HOST'] ?? 'N/A') . ']' . PHP_EOL;
echo 'SERVER_NAME = [' . ($_SERVER['SERVER_NAME'] ?? 'N/A') . ']' . PHP_EOL;
echo 'HOSTED env  = [' . (getenv('HOSTED') ?: 'not set') . ']' . PHP_EOL;
echo '</pre>';
