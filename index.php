<?php
header("Access-Control-Allow-Origin: *");
define('APP__ROOT', getcwd() ?: __DIR__ . '/../..');
require(APP__ROOT . '/vendor/autoload.php');

if (is_file(APP__ROOT . '/.env')) {
    $dotenv = \Dotenv\Dotenv::create(APP__ROOT . '/');
    $dotenv->load();
}

$app = \extas\components\api\App::create();
$app->run();
