<?php
namespace tests;

use extas\components\repositories\TSnuffRepository;
use extas\components\workflows\entities\EntityRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\entities\EntitySample;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\components\plugins\workflows\views\schemas\ViewSchemaSave;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewSchemaSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewSchemaSaveTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->registerSnuffRepos([
            'workflowSchemaRepository' => SchemaRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowEntitySampleRepository' => EntitySampleRepository::class,
            'workflowEntityRepository' => EntityRepository::class,
            'workflowStateRepository' => StateRepository::class
        ]);
        $this->createWithSnuffRepo('workflowEntitySampleRepository', new EntitySample([
            EntitySample::FIELD__NAME => 'new'
        ]));
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
    }

    public function testUpdateSchema()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
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
        $schema = $this->oneSnuffRepos('workflowSchemaRepository', [Schema::FIELD__NAME => 'test']);
        $this->assertNotEmpty($schema);
        $this->assertEquals('new', $schema->getEntity()->getSampleName());
    }
}
