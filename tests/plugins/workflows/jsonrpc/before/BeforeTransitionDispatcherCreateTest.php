<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplate;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplateRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\plugins\workflows\jsonrpc\before\transitions\dispatchers\BeforeTransitionDispatcherCreate;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\components\workflows\schemas\WorkflowSchemaRepository;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use Slim\Http\Response as PsrResponse;

/**
 * Class BeforeTransitionDispatcherCreateTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDispatcherCreateTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionTemplateRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->transitionRepo = new WorkflowTransitionRepository();
        $this->transitionTemplateRepo = new TransitionDispatcherTemplateRepository();
        $this->schemaRepo = new WorkflowSchemaRepository();

        SystemContainer::addItem(
            IWorkflowTransitionRepository::class,
            WorkflowTransitionRepository::class
        );
        SystemContainer::addItem(
            ITransitionDispatcherTemplateRepository::class,
            TransitionDispatcherTemplateRepository::class
        );
        SystemContainer::addItem(
            IWorkflowSchemaRepository::class,
            WorkflowSchemaRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
        $this->transitionTemplateRepo->delete([TransitionDispatcherTemplate::FIELD__NAME => 'test']);
        $this->schemaRepo->delete([WorkflowSchema::FIELD__NAME => 'test']);
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
    public function testUnknownSchema()
    {
        $operation = new BeforeTransitionDispatcherCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                TransitionDispatcher::FIELD__NAME => 'test',
                TransitionDispatcher::FIELD__SCHEMA_NAME => 'unknown',
                TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
                TransitionDispatcher::FIELD__TEMPLATE => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test'
        ]));

        $this->transitionTemplateRepo->create(new TransitionDispatcherTemplate([
            TransitionDispatcherTemplate::FIELD__NAME => 'test'
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
    public function testUnknownTransition()
    {
        $operation = new BeforeTransitionDispatcherCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                TransitionDispatcher::FIELD__NAME => 'test',
                TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
                TransitionDispatcher::FIELD__TRANSITION_NAME => 'unknown',
                TransitionDispatcher::FIELD__TEMPLATE => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test'
        ]));

        $this->transitionTemplateRepo->create(new TransitionDispatcherTemplate([
            TransitionDispatcherTemplate::FIELD__NAME => 'test'
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
    public function testUnknownTemplate()
    {
        $operation = new BeforeTransitionDispatcherCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                TransitionDispatcher::FIELD__NAME => 'test',
                TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
                TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
                TransitionDispatcher::FIELD__TEMPLATE => 'unknown'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test'
        ]));

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test'
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
        $operation = new BeforeTransitionDispatcherCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                TransitionDispatcher::FIELD__NAME => 'test',
                TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
                TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
                TransitionDispatcher::FIELD__TEMPLATE => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test'
        ]));

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test'
        ]));

        $this->transitionTemplateRepo->create(new TransitionDispatcherTemplate([
            TransitionDispatcherTemplate::FIELD__NAME => 'test'
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
