<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\plugins\workflows\jsonrpc\before\transitions\BeforeTransitionCreate;
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
 * Class BeforeTransitionCreateTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeTransitionCreateTest extends TestCase
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
        $this->stateRepo->delete([WorkflowState::FIELD__TITLE => 'test']);
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
    public function testTheSameFromToStates()
    {
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowTransition::FIELD__STATE_FROM => 'test',
                WorkflowTransition::FIELD__STATE_TO => 'test'
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

    /**
     * @throws
     */
    public function testUnknownStateFrom()
    {
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowTransition::FIELD__STATE_FROM => 'unknown',
                WorkflowTransition::FIELD__STATE_TO => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test',
            WorkflowState::FIELD__TITLE => 'test'
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
    public function testUnknownStateTo()
    {
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowTransition::FIELD__STATE_FROM => 'test',
                WorkflowTransition::FIELD__STATE_TO => 'unknown'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test',
            WorkflowState::FIELD__TITLE => 'test'
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
    public function testValid()
    {
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowTransition::FIELD__STATE_FROM => 'from',
                WorkflowTransition::FIELD__STATE_TO => 'to'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'from',
            WorkflowState::FIELD__TITLE => 'test'
        ]));

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'to',
            WorkflowState::FIELD__TITLE => 'test'
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
}