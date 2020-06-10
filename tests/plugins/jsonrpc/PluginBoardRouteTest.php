<?php
namespace tests\plugins\jsonrpc;

use extas\components\repositories\TSnuffRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\http\TSnuffHttp;
use extas\components\jsonrpc\App;
use extas\components\plugins\jsonrpc\PluginBoardRoute;
use extas\components\plugins\Plugin;
use extas\components\plugins\PluginRepository;
use extas\components\plugins\workflows\views\ViewIndexIndex;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class PluginBoardRouteTest
 *
 * @author jeyroik@gmail.com
 */
class PluginBoardRouteTest extends TestCase
{
    use TSnuffRepository;
    use TSnuffHttp;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->registerSnuffRepos([
            'pluginRepository' => PluginRepository::class,
            'workflowSchemaRepository' => SchemaRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowStateRepository' => StateRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
    }

    public function testAddRoute()
    {
        $app = App::create();
        $plugin = new PluginBoardRoute();
        $plugin($app);
        $routes = $app->getRouteCollector()->getRoutes();
        /**
         * - /api/jsonrpc
         * - /specs
         * - /section/action/name
         */
        $this->assertCount(3, $routes);
    }

    public function testBoardIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $app = App::create();
        $plugin = new PluginBoardRoute();
        $plugin($app);
        $routes = $app->getRouteCollector()->getRoutes();

        $this->createWithSnuffRepo('pluginRepository', new Plugin([
            Plugin::FIELD__CLASS => ViewIndexIndex::class,
            Plugin::FIELD__STAGE => 'view.index.index'
        ]));
        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'Test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
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
