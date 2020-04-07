<?php

use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\states\ViewStateEdit;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\components\workflows\states\WorkflowStateRepository;
use extas\components\workflows\states\WorkflowState;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewStateEditTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStateEditTest extends TestCase
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

    public function testStateEdit()
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
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
    }
}