<?php

use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\transitions\ViewTransitionCreate;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\components\workflows\states\WorkflowStateRepository;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewTransitionCreateTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionCreateTest extends TestCase
{
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

        $this->transitionRepo = new WorkflowTransitionRepository();

        SystemContainer::addItem(
            IWorkflowTransitionRepository::class,
            WorkflowTransitionRepository::class
        );

        SystemContainer::addItem(
            IWorkflowStateRepository::class,
            WorkflowStateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
    }

    public function testTransitionsIndex()
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

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewTransitionCreate();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);
    }
}
