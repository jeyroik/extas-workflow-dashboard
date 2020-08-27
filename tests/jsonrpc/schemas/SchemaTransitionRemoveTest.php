<?php
namespace tests\jsonrpc\schemas;

use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\interfaces\workflows\schemas\ISchema;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\components\jsonrpc\schemas\SchemaTransitionRemove;

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

/**
 * Class SchemaTransitionRemoveTest
 *
 * @author jeyroik@gmail.com
 */
class SchemaTransitionRemoveTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['workflowTransitionsDispatchers', 'name', TransitionDispatcher::class],
            ['workflowTransitions', 'name', Transition::class],
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
        $operation = new SchemaTransitionRemove([
            SchemaTransitionRemove::FIELD__PSR_REQUEST => $this->getPsrRequest('.trm.missed.schema'),
            SchemaTransitionRemove::FIELD__PSR_RESPONSE => $this->getPsrResponse()
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
    public function testUnknownTransition()
    {
        $operation = new SchemaTransitionRemove([
            SchemaTransitionRemove::FIELD__PSR_REQUEST => $this->getPsrRequest('.trm.missed.transition'),
            SchemaTransitionRemove::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->createSchema();

        $response = $operation();
        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new SchemaTransitionRemove([
            SchemaTransitionRemove::FIELD__PSR_REQUEST => $this->getPsrRequest('.trm'),
            SchemaTransitionRemove::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->createSchema();

        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__CLASS => '',
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test'
        ]));

        $response = $operation();
        $this->assertFalse(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );

        /**
         * @var ISchema $schema
         */
        $schema = $this->getMagicClass('workflowSchemas')->one([Schema::FIELD__NAME => 'test']);
        $this->assertFalse($schema->hasTransition('test'), 'Schema has transition');
    }

    protected function createSchema(): void
    {
        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
    }
}
