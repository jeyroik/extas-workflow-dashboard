<?php
namespace extas\components\plugins\jsonrpc;

use \extas\components\plugins\Plugin;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class PluginBoardRoute
 *
 * @author jeyroik <jeyroik@gmail.com>
 */
class PluginBoardRoute extends Plugin
{
    /**
     * @param \extas\components\jsonrpc\App $app
     */
    public function __invoke(\extas\components\jsonrpc\App &$app): void
    {
        $app->any('/[{section}[/{action}[/{name}]]]', function (Request $request, Response $response, array $args) {
            $pluginStub = new Plugin();
            $section = $args['section'] ?? 'index';
            $action = $args['action'] ?? 'index';
            foreach ($pluginStub->getPluginsByStage('view.' . $section . '.' . $action) as $plugin) {
                $plugin($request, $response, $args);
            }
            return $response;
        });
    }
}
