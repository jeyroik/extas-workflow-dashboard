<?php

use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\states\ViewStateEdit;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\components\workflows\states\WorkflowStateRepository;
use extas\components\workflows\states\WorkflowState;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewStateSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStateSaveTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->stateRepo = new WorkflowStateRepository();

        SystemContainer::addItem(
            IWorkflowStateRepository::class,
            WorkflowStateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->stateRepo->delete([WorkflowState::FIELD__TITLE => 'test']);
    }

    public function testStateUpdate()
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

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test',
            WorkflowState::FIELD__TITLE => 'test'
        ]));
        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test2',
            WorkflowState::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewStateEdit();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);

        /**
         * @var WorkflowState $state
         */
        $state = $this->stateRepo->one([WorkflowState::FIELD__NAME => 'test']);
        $this->assertEquals('test', $state->getDescription());
    }

    public function testStateCreateOnUpdateIfNotExists()
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

        $this->schemaRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test',
            WorkflowState::FIELD__TITLE => 'test'
        ]));
        $this->schemaRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test2',
            WorkflowState::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewStateEdit();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'unknown']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
        $this->assertNotEmpty($this->stateRepo->one([WorkflowState::FIELD__DESCRIPTION => 'test']));
    }
}
