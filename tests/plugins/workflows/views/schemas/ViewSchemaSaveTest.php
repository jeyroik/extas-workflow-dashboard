<?php

use PHPUnit\Framework\TestCase;
use extas\components\jsonrpc\App;
use extas\components\plugins\jsonrpc\PluginBoardRoute;
use \extas\components\plugins\Plugin;
use \extas\components\plugins\PluginRepository;
use extas\components\plugins\workflows\views\schemas\ViewSchemaSave;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\components\workflows\schemas\WorkflowSchemaRepository;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewSchemaSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewSchemaSaveTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $pluginRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->pluginRepo = new PluginRepository();
        $this->schemaRepo = new WorkflowSchemaRepository();
        $this->transitionRepo = new WorkflowTransitionRepository();

        SystemContainer::addItem(
            IWorkflowSchemaRepository::class,
            WorkflowSchemaRepository::class
        );
        SystemContainer::addItem(
            IWorkflowTransitionRepository::class,
            WorkflowTransitionRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->pluginRepo->delete([Plugin::FIELD__CLASS => ViewSchemaSave::class]);
        $this->schemaRepo->delete([WorkflowSchema::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
    }

    public function testUpdateSchema()
    {
        $request = new \Slim\Http\Request(
            'GET',
            new \Slim\Http\Uri('http', 'localhost', 80, '/'),
            new \Slim\Http\Headers(['Content-type' => 'text/html']),
            [],
            [],
            new \Slim\Http\Stream(fopen('php://input', 'r'))
        );

        $response = new \Slim\Http\Response();

        $this->pluginRepo->create(new Plugin([
            Plugin::FIELD__CLASS => ViewSchemaSave::class,
            Plugin::FIELD__STAGE => 'view.schema.save'
        ]));
        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__TITLE => 'Test',
            WorkflowSchema::FIELD__DESCRIPTION => 'Test',
            WorkflowSchema::FIELD__TRANSITIONS => ['test'],
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test'
        ]));
        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__TITLE => 'Test',
            WorkflowTransition::FIELD__STATE_FROM => 'from',
            WorkflowTransition::FIELD__STATE_TO => 'to'
        ]));

        $_REQUEST['transitions'] = 'test';
        $_REQUEST['entity_template'] = 'new';
        $dispatcher = new ViewSchemaSave();
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Схемы</title>') !== false);

        /**
         * @var WorkflowSchema $schema
         */
        $schema = $this->schemaRepo->one([WorkflowSchema::FIELD__NAME => 'test']);
        $this->assertNotEmpty($schema);
        $this->assertEquals('new', $schema->getEntityTemplateName());
    }
}
