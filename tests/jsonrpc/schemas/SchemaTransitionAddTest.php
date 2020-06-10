<?php
namespace tests\jsonrpc\schemas;

use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;

use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepository;
use extas\components\workflows\transitions\TransitionSample;
use extas\components\workflows\transitions\TransitionSampleRepository;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\components\jsonrpc\schemas\SchemaTransitionAdd;
use extas\components\workflows\schemas\SchemaRepository;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class SchemaTransitionAddTest
 *
 * @author jeyroik@gmail.com
 */
class SchemaTransitionAddTest extends TestCase
{
    use TSnuffRepository;
    use TSnuffHttp;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->registerSnuffRepos([
            'workflowTransitionDispatcherRepository' => TransitionDispatcherRepository::class,
            'workflowTransitionDispatcherSampleRepository' => TransitionDispatcherSampleRepository::class,
            'workflowEntitySampleRepository' => EntitySampleRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowTransitionSampleRepository' => TransitionSampleRepository::class,
            'workflowSchemaRepository' => SchemaRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
    }

    /**
     * @throws
     */
    public function testUnknownSchema()
    {
        $operation = new SchemaTransitionAdd([
            SchemaTransitionAdd::FIELD__PSR_REQUEST => $this->getPsrRequest('.trad.missed.schema'),
            SchemaTransitionAdd::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $response = $operation();

        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    /**
     * @throws
     */
    public function testSchemaHasTransition()
    {
        $operation = new SchemaTransitionAdd([
            SchemaTransitionAdd::FIELD__PSR_REQUEST => $this->getPsrRequest('.trad.has.transition'),
            SchemaTransitionAdd::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createSchema();

        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__SAMPLE_NAME => 'test',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->createWithSnuffRepo('workflowTransitionSampleRepository', new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $response = $operation();
        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    /**
     * @throws
     */
    public function testTransitionWithDispatchers()
    {
        $operation = new SchemaTransitionAdd([
            SchemaTransitionAdd::FIELD__PSR_REQUEST => $this->getPsrRequest('.trad.with.dispatchers'),
            SchemaTransitionAdd::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createSchema();

        $this->createWithSnuffRepo('workflowTransitionSampleRepository', new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $response = $operation();
        $this->assertFalse(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );

        $dispatcher = $this->oneSnuffRepos(
            'workflowTransitionDispatcherRepository',
            [ITransitionDispatcher::FIELD__NAME => 'test']
        );
        $this->assertNotEmpty($dispatcher, 'Dispatcher is not removed');
    }

    protected function createSchema(): void
    {
        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
    }
}
