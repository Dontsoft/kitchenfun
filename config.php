<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

define('API_KEY', getenv('API_KEY'));
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD'));
define('MAIL_USERNAME', getenv('MAIL_USERNAME'));
define('MAIL_SERVER', getenv('MAIL_SERVER'));
define('MAIL_PORT', getenv('MAIL_PORT'));
define('MAIL_FROM', getenv('MAIL_FROM'));
define('MAIL_FROM_MAIL', getenv('MAIL_FROM_MAIL'));