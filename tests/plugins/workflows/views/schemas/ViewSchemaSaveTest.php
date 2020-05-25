<?php
namespace tests;

use Dotenv\Dotenv;
use extas\components\workflows\entities\EntityRepository;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\entities\EntitySample;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\interfaces\workflows\entities\IEntitySampleRepository;
use extas\components\plugins\workflows\views\schemas\ViewSchemaSave;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewSchemaSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewSchemaSaveTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffExtensions;

    protected IRepository $schemaRepo;
    protected IRepository $transitionRepo;
    protected IRepository $entitySampleRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->schemaRepo = new SchemaRepository();
        $this->transitionRepo = new TransitionRepository();
        $this->entitySampleRepo = new EntitySampleRepository();
        $this->addReposForExt([
            'workflowSchemaRepository' => SchemaRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowEntitySampleRepository' => EntitySampleRepository::class,
            'workflowEntityRepository' => EntityRepository::class
        ]);
        $this->entitySampleRepo->create(new EntitySample([
            EntitySample::FIELD__NAME => 'new'
        ]));
    }

    public function tearDown(): void
    {
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->entitySampleRepo->delete([EntitySample::FIELD__NAME => 'new']);
        $this->deleteSnuffExtensions();
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
        $this->assertEquals('new', $schema->getEntity()->getSampleName());
    }
}
