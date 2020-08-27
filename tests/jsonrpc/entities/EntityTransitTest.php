<?php
namespace tests\jsonrpc\entities;

use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSample;
use extas\interfaces\samples\parameters\ISampleParameter;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\entities\Entity;
use extas\components\jsonrpc\entities\EntityTransit;

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
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['workflowTransitionsDispatchers', 'name', TransitionDispatcher::class],
            ['workflowTransitionsDispatchersSamples', 'name', TransitionDispatcherSample::class],
            ['workflowEntities', 'name', Entity::class],
            ['workflowTransitions', 'name', Transition::class],
            ['workflowSchemas', 'name', Schema::class],
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
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

        $this->getMagicClass('workflowEntities')->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->getMagicClass('workflowSchemas')->create(new Schema([
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

        $this->getMagicClass('workflowEntities')->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->getMagicClass('workflowTransitions')->create(new Transition([
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
        $this->getMagicClass('workflowEntities')->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
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

        $this->getMagicClass('workflowEntities')->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->getMagicClass('workflowTransitions')->create(new Transition([
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
