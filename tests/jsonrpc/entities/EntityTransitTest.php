<?php
namespace tests\jsonrpc\entities;

use extas\interfaces\samples\parameters\ISampleParameter;
use extas\interfaces\jsonrpc\IResponse;

use extas\components\repositories\TSnuffRepository;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\entities\EntityRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\components\workflows\entities\Entity;
use extas\components\jsonrpc\entities\EntityTransit;
use extas\components\workflows\schemas\SchemaRepository;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityTransitTest
 *
 * @author jeyroik@gmail.com
 */
class EntityTransitTest extends TestCase
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
            'workflowEntityRepository' => EntityRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowSchemaRepository' => SchemaRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
    }

    public function testUnknownSchema()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest('.entity.transit.schema.unknown'),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testUnknownTransition()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.entity.transit.transition.unknown'
            ),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createWithSnuffRepo('workflowEntityRepository', new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testInvalidEntityState()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.entity.transit.state.invalid'
            ),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createWithSnuffRepo('workflowEntityRepository', new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

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

        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testInvalidEntityContent()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.entity.transit.content.invalid'
            ),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->createWithSnuffRepo('workflowEntityRepository', new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

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

        $this->createWithSnuffRepo('workflowTransitionDispatcherRepository', new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__SAMPLE_NAME => 'test',
            TransitionDispatcher::FIELD__CLASS =>
                'extas\\components\\workflows\\transitions\\dispatchers\\EntityHasAllParams',
            TransitionDispatcher::FIELD__PARAMETERS => [
                [ISampleParameter::FIELD__NAME => 'test']
            ]
        ]));

        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testValid()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest('.entity.transit.valid'),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createWithSnuffRepo('workflowEntityRepository', new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test',
            Transition::FIELD__SAMPLE_NAME => 'test'
        ]));

        $response = $operation();

        $this->assertFalse(
            $this->isJsonRpcResponseHasError($response, IResponse::RESPONSE__ERROR),
            'Response has error. Response: ' . json_encode($this->getJsonRpcResponse($response))
        );
    }
}
