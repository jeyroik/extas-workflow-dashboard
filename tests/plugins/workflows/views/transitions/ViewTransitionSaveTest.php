<?php

use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\transitions\ViewTransitionSave;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewTransitionSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionSaveTest extends TestCase
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
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([WorkflowTransition::FIELD__TITLE => 'test']);
    }

    public function testTransitionUpdate()
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
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__TITLE => 'test'
        ]));
        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test2',
            WorkflowTransition::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewTransitionSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);

        /**
         * @var WorkflowTransition $transition
         */
        $transition = $this->transitionRepo->one([WorkflowTransition::FIELD__NAME => 'test']);
        $this->assertEquals('test', $transition->getDescription());
    }

    public function testTransitionCreateOnUpdateIfNotExists()
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
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__TITLE => 'test'
        ]));
        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test2',
            WorkflowTransition::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewTransitionSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'unknown']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);
        $this->assertNotEmpty($this->transitionRepo->one([WorkflowTransition::FIELD__DESCRIPTION => 'test']));
    }
}
