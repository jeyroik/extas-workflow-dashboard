<?php

use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\schemas\ViewSchemaEdit;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;
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
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
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

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__TRANSITIONS => ['test'],
            Schema::FIELD__ENTITY_TEMPLATE => 'test'
        ]));
        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'Test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to'
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

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__TRANSITIONS => ['test'],
            Schema::FIELD__ENTITY_TEMPLATE => 'test'
        ]));
        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'Test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to'
        ]));

        $_REQUEST['transitions'] = 'test';
        $_REQUEST['entity_template'] = 'new';
        $dispatcher = new ViewSchemaEdit();
        $dispatcher($request, $response, ['name' => 'unknown']);
        $this->assertEquals(302, $response->getStatusCode());
    }
}
