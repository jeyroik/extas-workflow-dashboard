<?php

use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\schemas\ViewSchemaEdit;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\components\workflows\schemas\WorkflowSchemaRepository;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewSchemaEditTest
 *
 * @author jeyroik@gmail.com
 */
class ViewSchemaEditTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

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
        $this->schemaRepo->delete([WorkflowSchema::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
    }

    public function testEditingSchema()
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
        $dispatcher = new ViewSchemaEdit();
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Схемы - Редактирование</title>') !== false);
    }

    public function testRedirectOnEmptySchema()
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
        $dispatcher = new ViewSchemaEdit();
        $dispatcher($request, $response, ['name' => 'unknown']);
        $this->assertEquals(302, $response->getStatusCode());
    }
}
