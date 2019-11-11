<?php
require(__DIR__ . '/../../vendor/autoload.php');

define('APP__ROOT', __DIR__ . '/../..');

if (is_file(__DIR__ . '/../../.env')) {
    $dotenv = \Dotenv\Dotenv::create(__DIR__ . '/../../');
    $dotenv->load();
}

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;
$app->post('/api/jsonrpc', function (Request $request, Response $response, array $args) {
    $jrpcRequest = json_decode($request->getBody()->getContents(), true);
    $method = $jrpcRequest['method'] ?? 'app.index';
    $pluginStub = new \extas\components\plugins\Plugin();
    foreach ($pluginStub->getPluginsByStage('run.jsonrpc.' . $method) as $plugin) {
        $plugin($request, $response, $jrpcRequest);
    }
});

$app->any('/specs/', function (Request $request, Response $response, array $args) {
    $jrpcRequest = json_decode($request->getBody()->getContents(), true);
    $method = $jrpcRequest['method'] ?? 'app.index';
    $pluginStub = new \extas\components\plugins\Plugin();
    foreach ($pluginStub->getPluginsByStage('run.specs.' . $method) as $plugin) {
        $plugin($request, $response, $jrpcRequest);
    }
});

$app->run();