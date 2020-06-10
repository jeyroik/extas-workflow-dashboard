<?php
namespace tests\jsonrpc\schemas;

use extas\interfaces\workflows\schemas\ISchema;

use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\jsonrpc\schemas\SchemaTransitionRemove;
use extas\components\workflows\schemas\SchemaRepository;

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
    use TSnuffRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->registerSnuffRepos([
            'workflowTransitionDispatcherRepository' => TransitionDispatcherRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
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

        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->createWithSnuffRepo('workflowTransitionDispatcherRepository', new TransitionDispatcher([
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
        $schema = $this->oneSnuffRepos('workflowSchemaRepository' , [Schema::FIELD__NAME => 'test']);
        $this->assertFalse($schema->hasTransition('test'), 'Schema has transition');
    }

    protected function createSchema(): void
    {
        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
    }
}
