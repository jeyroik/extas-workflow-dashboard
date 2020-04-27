<?php

use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\states\ViewStateCreate;
use extas\interfaces\workflows\states\IStateRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewStateCreateTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStateCreateTest extends TestCase
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

        $this->stateRepo = new StateRepository();

        SystemContainer::addItem(
            IStateRepository::class,
            StateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->stateRepo->delete([State::FIELD__NAME => 'test']);
    }

    public function testStateCreateView()
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

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewStateCreate();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
    }
}
