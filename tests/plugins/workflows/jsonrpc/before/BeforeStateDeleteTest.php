<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\plugins\workflows\jsonrpc\before\states\BeforeStateDelete;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\components\workflows\states\WorkflowStateRepository;
use extas\components\workflows\states\WorkflowState;
use Slim\Http\Response as PsrResponse;

/**
 * Class BeforeStateDeleteTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeStateDeleteTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->transitionRepo = new WorkflowTransitionRepository();
        $this->stateRepo = new WorkflowStateRepository();

        SystemContainer::addItem(
            IWorkflowTransitionRepository::class,
            WorkflowTransitionRepository::class
        );
        SystemContainer::addItem(
            IWorkflowStateRepository::class,
            WorkflowStateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
        $this->stateRepo->delete([WorkflowState::FIELD__NAME => 'test']);
    }

    protected function getServerRequest(array $params)
    {
        return new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => IRequest::SUBJECT,
                    IParameter::FIELD__VALUE => new Request([
                        IRequest::FIELD__PARAMS => $params
                    ])
                ]
            ]
        ]);
    }

    protected function getServerResponse()
    {
        return new ServerResponse([
            ServerResponse::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => IResponse::SUBJECT,
                    IParameter::FIELD__VALUE => new Response([
                        Response::FIELD__RESPONSE => new PsrResponse()
                    ])
                ]
            ]
        ]);
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new BeforeStateDelete();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowState::FIELD__NAME => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());
    }

    /**
     * @throws
     */
    public function testHasTransitionsTo()
    {
        $operation = new BeforeStateDelete();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowState::FIELD__NAME => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test'
        ]));

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__STATE_FROM => 'from',
            WorkflowTransition::FIELD__STATE_TO => 'test'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    /**
     * @throws
     */
    public function testHasTransitionsFrom()
    {
        $operation = new BeforeStateDelete();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowState::FIELD__NAME => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test'
        ]));

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__STATE_FROM => 'test',
            WorkflowTransition::FIELD__STATE_TO => 'to'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    /**
     * @throws
     */
    public function testUnknownState()
    {
        $operation = new BeforeStateDelete();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowState::FIELD__NAME => 'unknown'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertTrue($jsonRpcResponse->hasError());
    }
}
