<?php
namespace tests\jsonrpc\transitions;

use extas\interfaces\jsonrpc\IResponse;

use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepository;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\components\jsonrpc\transitions\TransitionByStateFrom;
use extas\components\workflows\schemas\SchemaRepository;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class TransitionByStateFromTest
 *
 * @author jeyroik@gmail.com
 */
class TransitionByStateFromTest extends TestCase
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
            'workflowTransitionDispatcherSampleRepository' => TransitionDispatcherSampleRepository::class,
            'workflowEntitySampleRepository' => EntitySampleRepository::class,
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
        $operation = new TransitionByStateFrom([
            TransitionByStateFrom::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transition.by.state.unknown.schema'
            ),
            TransitionByStateFrom::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->assertTrue($this->isJsonRpcResponseHasError($operation()));
    }

    /**
     * @throws
     */
    public function testUnknownEntityTemplate()
    {
        $operation = new TransitionByStateFrom([
            TransitionByStateFrom::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transition.by.state.unknown.entity'
            ),
            TransitionByStateFrom::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->assertTrue($this->isJsonRpcResponseHasError($operation()));
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new TransitionByStateFrom([
            TransitionByStateFrom::FIELD__PSR_REQUEST => $this->getPsrRequest('.transition.by.state'),
            TransitionByStateFrom::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test2',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $response = $operation();

        /**
         * @var $jsonRpcResponse IResponse
         * @var $schema Schema
         */
        $jsonRpcResponse = $this->getJsonRpcResponse($response);
        $this->assertFalse(isset($jsonRpcResponse[IResponse::RESPONSE__ERROR]));
        $this->assertEquals(
            [
                IResponse::RESPONSE__ID => '2f5d0719-5b82-4280-9b3b-10f23aff226b',
                IResponse::RESPONSE__VERSION => IResponse::VERSION_CURRENT,
                IResponse::RESPONSE__RESULT => [
                    [
                        Transition::FIELD__NAME => 'test',
                        Transition::FIELD__STATE_FROM => 'from',
                        Transition::FIELD__STATE_TO => 'to',
                        Transition::FIELD__SCHEMA_NAME => 'test'
                    ]
                ]
            ],
            $jsonRpcResponse
        );
    }
}
