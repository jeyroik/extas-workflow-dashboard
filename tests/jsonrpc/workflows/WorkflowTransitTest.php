<?php
namespace tests\jsonrpc\workflows;

use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\samples\parameters\ISampleParameter;

use extas\components\http\TSnuffHttp;
use extas\components\jsonrpc\workflows\WorkflowTransit;
use extas\components\repositories\TSnuffRepository;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityRepository;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\dispatchers\ContextHasAllParams;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class WorkflowTransitTest
 *
 * @package tests\jsonrpc\workflows
 * @author jeyroik <jeyroik@gmail.com>
 */
class WorkflowTransitTest extends TestCase
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

    public function testUnknownTransition()
    {
        $operation = new WorkflowTransit([
            WorkflowTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transit.transition.missed'
            ),
            WorkflowTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $response = $operation();
        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response, IResponse::RESPONSE__ERROR),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    public function testUnknownEntity()
    {
        $operation = new WorkflowTransit([
            WorkflowTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transit'
            ),
            WorkflowTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createTransition();

        $response = $operation();
        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response, IResponse::RESPONSE__ERROR),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    public function testMissedEntityFields()
    {
        $operation = new WorkflowTransit([
            WorkflowTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transit'
            ),
            WorkflowTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createTransition();
        $this->createEntity([
            'unknown' => [
                ISampleParameter::FIELD__NAME => 'unknown'
            ]
        ]);

        $response = $operation();
        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response, IResponse::RESPONSE__ERROR),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    public function testHasTransitionErrors()
    {
        $operation = new WorkflowTransit([
            WorkflowTransit::FIELD__PSR_REQUEST => $this->getPsrRequest('.transit'),
            WorkflowTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createEntity();
        $this->createCondition([
            'missed' => [
                ISampleParameter::FIELD__NAME => 'missed'
            ]
        ]);
        $this->createTransition();

        $response = $operation();
        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response, IResponse::RESPONSE__ERROR),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    public function testValid()
    {
        $operation = new WorkflowTransit([
            WorkflowTransit::FIELD__PSR_REQUEST => $this->getPsrRequest('.transit'),
            WorkflowTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createCondition([
            'known' => [
                ISampleParameter::FIELD__NAME => 'known'
            ]
        ]);
        $this->createTransition();
        $this->createEntity([
            'test' => [
                ISampleParameter::FIELD__NAME => 'test'
            ]
        ]);

        $response = $operation();
        $this->assertFalse(
            $this->isJsonRpcResponseHasError($response, IResponse::RESPONSE__ERROR),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    protected function createEntity(array $params = []): void
    {
        $this->createWithSnuffRepo('workflowEntityRepository', new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__PARAMETERS => $params
        ]));
    }

    /**
     * Create transition in a repo.
     */
    protected function createTransition(): void
    {
        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test',
            Transition::FIELD__CONDITIONS_NAMES => ['test']
        ]));
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    protected function createCondition(array $params): void
    {
        $this->createWithSnuffRepo('workflowTransitionDispatcherRepository', new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__CLASS => ContextHasAllParams::class,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__PARAMETERS => $params
        ]));
    }
}
