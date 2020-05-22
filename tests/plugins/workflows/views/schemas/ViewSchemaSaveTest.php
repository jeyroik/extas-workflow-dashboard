<?php
namespace tests;

use Dotenv\Dotenv;
use extas\components\http\TSnuffHttp;
use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\schemas\ViewSchemaSave;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;
use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewSchemaSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewSchemaSaveTest extends TestCase
{
    use TSnuffHttp;

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
        $env = Dotenv::create(getcwd() . '/tests/');
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

    public function testUpdateSchema()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

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

        $_REQUEST['transitions'] = 'test';
        $_REQUEST['entity_name'] = 'new';

        $dispatcher = new ViewSchemaSave();
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Схемы</title>') !== false);

        /**
         * @var Schema $schema
         */
        $schema = $this->schemaRepo->one([Schema::FIELD__NAME => 'test']);
        $this->assertNotEmpty($schema);
        $this->assertEquals('new', $schema->getEntityName());
    }
}
