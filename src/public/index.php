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
    foreach ($pluginStub->getPluginsByStage('before.run.jsonrpc.' . $method) as $plugin) {
        $plugin($request, $response, $jrpcRequest);
    }
    if (!isset($jrpcRequest[\extas\components\jsonrpc\JsonRpcErrors::ERROR__MARKER])) {
        foreach ($pluginStub->getPluginsByStage('run.jsonrpc.' . $method) as $plugin) {
            $plugin($request, $response, $jrpcRequest);
        }
        foreach ($pluginStub->getPluginsByStage('after.run.jsonrpc.' . $method) as $plugin) {
            $plugin($request, $response, $jrpcRequest);
        }
    }
    return $response;
});

$app->any('/specs/', function (Request $request, Response $response, array $args) {
    $jrpcRequest = json_decode($request->getBody()->getContents(), true);
    $method = $jrpcRequest['method'] ?? 'app.index';
    $pluginStub = new \extas\components\plugins\Plugin();
    foreach ($pluginStub->getPluginsByStage('run.specs.' . $method) as $plugin) {
        $plugin($request, $response, $jrpcRequest);
    }
    return $response;
});

$app->get('/[{section}[/{action}[/{name}]]]', function (Request $request, Response $response, array $args) {
    $pluginStub = new \extas\components\plugins\Plugin();
    $section = $args['section'] ?? 'index';
    $action = $args['action'] ?? 'index';
    foreach ($pluginStub->getPluginsByStage('view.' . $section . '.' . $action) as $plugin) {
        $plugin($request, $response, $args);
    }
    return $response;
});

$app->run();