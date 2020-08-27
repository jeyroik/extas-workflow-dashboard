<?php
namespace tests;

use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntitySample;
use extas\components\plugins\workflows\views\schemas\ViewSchemaSave;
use extas\components\workflows\states\State;
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
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->createSnuffDynamicRepositories([
            ['workflowSchemas', 'name', Schema::class],
            ['workflowTransitions', 'name', Transition::class],
            ['workflowEntitiesSamples', 'name', EntitySample::class],
            ['workflowEntities', 'name', Entity::class],
            ['workflowStates', 'name', State::class],
        ]);

        $this->getMagicClass('workflowEntitiesSamples')->create(new EntitySample([
            EntitySample::FIELD__NAME => 'new'
        ]));
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testUpdateSchema()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
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
        $schema = $this->getMagicClass('workflowSchemas')->one([Schema::FIELD__NAME => 'test']);
        $this->assertNotEmpty($schema);
        $this->assertEquals('new', $schema->getEntity()->getSampleName());
    }
}
