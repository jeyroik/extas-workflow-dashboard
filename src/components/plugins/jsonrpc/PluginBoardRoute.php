<?php
namespace extas\components\plugins\jsonrpc;

use extas\components\Plugins;
use extas\components\plugins\Plugin;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;

/**
 * Class PluginBoardRoute
 *
 * @author jeyroik <jeyroik@gmail.com>
 */
class PluginBoardRoute extends Plugin
{
    /**
     * @param App $app
     */
    public function __invoke(App &$app): void
    {
        $app->any(
            '/[{section}[/{action}[/{name}]]]',
            function (Request $request, Response $response, array $args) {
                $section = $args['section'] ?? 'index';
                $action = $args['action'] ?? 'index';
                foreach (Plugins::byStage('view.' . $section . '.' . $action) as $plugin) {
                    $plugin($request, $response, $args);
                }
                return $response;
            }
        );
    }
}
