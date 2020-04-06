<?php

use PHPUnit\Framework\TestCase;
use extas\components\jsonrpc\App;
use extas\components\plugins\jsonrpc\PluginBoardRoute;

/**
 * Class PluginBoardRouteTest
 *
 * @author jeyroik@gmail.com
 */
class PluginBoardRouteTest extends TestCase
{
    public function testAddRoute()
    {
        $app = new App();
        $plugin = new PluginBoardRoute();
        $plugin($app);
        $container = $app->getContainer();
        $router = $container->get('router');
        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
    }
}
