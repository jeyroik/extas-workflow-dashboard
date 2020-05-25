<?php
namespace tests\plugins\jsonrpc;

use Dotenv\Dotenv;
use extas\components\workflows\states\StateRepository;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
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
use extas\interfaces\repositories\IRepository;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

/**
 * Class PluginBoardRouteTest
 *
 * @author jeyroik@gmail.com
 */
class PluginBoardRouteTest extends TestCase
{
    use TSnuffExtensions;
    use TSnuffHttp;

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
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->pluginRepo = new PluginRepository();
        $this->schemaRepo = new SchemaRepository();
        $this->transitionRepo = new TransitionRepository();
        $this->addReposForExt([
            'workflowSchemaRepository' => SchemaRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowStateRepository' => StateRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->pluginRepo->delete([Plugin::FIELD__CLASS => ViewIndexIndex::class]);
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->deleteSnuffExtensions();
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
        $request = new Request(
            'GET',
            new Uri('http', 'localhost', 80, '/'),
            new Headers(['Content-type' => 'text/html']),
            [],
            [],
            new Stream(fopen('php://input', 'r'))
        );

        $response = $this->getPsrResponse();

        $app = App::create();
        $plugin = new PluginBoardRoute();
        $plugin($app);
        $routes = $app->getRouteCollector()->getRoutes();

        $this->pluginRepo->create(new Plugin([
            Plugin::FIELD__CLASS => ViewIndexIndex::class,
            Plugin::FIELD__STAGE => 'view.index.index'
        ]));
        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->transitionRepo->create(new Transition([
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
