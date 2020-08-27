<?php
namespace tests\jsonrpc\schemas;

use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\entities\EntitySample;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSample;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\transitions\TransitionSample;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\Transition;
use extas\components\jsonrpc\schemas\SchemaTransitionAdd;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class SchemaTransitionAddTest
 *
 * @author jeyroik@gmail.com
 */
class SchemaTransitionAddTest extends TestCase
{
    use TSnuffRepositoryDynamic;
    use TSnuffHttp;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['workflowTransitionsDispatchers', 'name', TransitionDispatcher::class],
            ['workflowTransitionsDispatchersSamples', 'name', TransitionDispatcherSample::class],
            ['workflowEntitiesSamples', 'name', EntitySample::class],
            ['workflowTransitions', 'name', Transition::class],
            ['workflowTransitionsSamples', 'name', TransitionSample::class],
            ['workflowSchemas', 'name', Schema::class],
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
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

        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__SAMPLE_NAME => 'test',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->getMagicClass('workflowTransitionsSamples')->create(new TransitionSample([
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

        $this->getMagicClass('workflowTransitionsSamples')->create(new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $response = $operation();
        $this->assertFalse(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );

        $dispatcher = $this->getMagicClass('workflowTransitionsDispatchers')
            ->one([ITransitionDispatcher::FIELD__NAME => 'test']);
        $this->assertNotEmpty($dispatcher, 'Dispatcher is not removed');
    }

    protected function createSchema(): void
    {
        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
    }
}
