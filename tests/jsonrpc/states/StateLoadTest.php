<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\SystemContainer;
use extas\interfaces\parameters\IParameter;
use extas\components\workflows\states\WorkflowState;
use extas\components\jsonrpc\states\StateLoad;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\components\workflows\states\WorkflowStateRepository;
use Slim\Http\Response as PsrResponse;

/**
 * Class StateLoadTest
 *
 * @author jeyroik@gmail.com
 */
class StateLoadTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->stateRepo = new WorkflowStateRepository();

        SystemContainer::addItem(
            IWorkflowStateRepository::class,
            WorkflowStateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->stateRepo->delete([WorkflowSchema::FIELD__TITLE => 'test']);
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
        $operation = new StateLoad();
        $serverRequest = $this->getServerRequest([
            'data' => [
                [
                    WorkflowState::FIELD__NAME => 'test',
                    WorkflowState::FIELD__TITLE => 'test'
                ],
                [
                    WorkflowState::FIELD__NAME => 'test2'
                ]
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new WorkflowState([
            WorkflowState::FIELD__NAME => 'test2',
            WorkflowState::FIELD__TITLE => 'test'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         * @var $schema WorkflowSchema
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());

        $this->assertEquals(
            [
                IResponse::RESPONSE__ID => $jsonRpcResponse->getData()[IRequest::FIELD__ID] ?? '',
                IResponse::RESPONSE__VERSION => IResponse::VERSION_CURRENT,
                IResponse::RESPONSE__RESULT => [
                    'created_count' => 1,
                    'got_count' => 2
                ]
            ],
            json_decode($jsonRpcResponse->getPsrResponse()->getBody(), true)
        );
    }
}
