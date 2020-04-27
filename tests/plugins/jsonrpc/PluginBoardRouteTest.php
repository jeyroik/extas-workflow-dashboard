<?php

use PHPUnit\Framework\TestCase;
use extas\components\jsonrpc\App;
use extas\components\plugins\jsonrpc\PluginBoardRoute;
use \extas\components\plugins\Plugin;
use \extas\components\plugins\PluginRepository;
use extas\components\plugins\workflows\views\ViewIndexIndex;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class PluginBoardRouteTest
 *
 * @author jeyroik@gmail.com
 */
class PluginBoardRouteTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $pluginRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

    protected ?IRepository $transitionRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->pluginRepo = new PluginRepository();
        $this->schemaRepo = new SchemaRepository();
        $this->transitionRepo = new TransitionRepository();

        SystemContainer::addItem(
            ISchemaRepository::class,
            SchemaRepository::class
        );
        SystemContainer::addItem(
            ITransitionRepository::class,
            TransitionRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->pluginRepo->delete([Plugin::FIELD__CLASS => ViewIndexIndex::class]);
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
    }

    public function testAddRoute()
    {
        $app = new App();
        $plugin = new PluginBoardRoute();
        $plugin($app);
        $container = $app->getContainer();
        $router = $container->get('router');
        $routes = $router->getRoutes();
        /**
         * - /api/jsonrpc
         * - /specs
         * - /section/action/name
         */
        $this->assertCount(3, $routes);
    }

    public function testBoardIndex()
    {
        $request = new \Slim\Http\Request(
            'GET',
            new \Slim\Http\Uri('http', 'localhost', 80, '/'),
            new \Slim\Http\Headers([
                'Content-type' => 'text/html'
            ]),
            [],
            [],
            new \Slim\Http\Stream(fopen('php://input', 'r'))
        );

        $response = new \Slim\Http\Response();

        $app = new App();
        $plugin = new PluginBoardRoute();
        $plugin($app);
        $container = $app->getContainer();
        /**
         * @var \Slim\Router $router
         */
        $router = $container->get('router');
        $routes = $router->getRoutes();

        $this->pluginRepo->create(new Plugin([
            Plugin::FIELD__CLASS => ViewIndexIndex::class,
            Plugin::FIELD__STAGE => 'view.index.index'
        ]));
        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__TRANSITIONS_NAMES => ['test'],
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'Test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to'
        ]));

        foreach ($routes as $route) {
            if ($route->getPattern() == '/[{section}[/{action}[/{name}]]]') {
                $dispatcher = $route->getCallable();
                $response = $dispatcher($request, $response, []);
            }
        }

        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();

        $this->assertTrue(strpos($page, '<title>Схемы</title>') !== false);
    }


}
