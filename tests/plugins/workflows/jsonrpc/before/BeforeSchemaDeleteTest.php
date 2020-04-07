<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\plugins\workflows\jsonrpc\before\schemas\BeforeSchemaCreate;
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
 * Class BeforeSchemaDeleteTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeSchemaDeleteTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->transitionRepo = new TransitionDispatcherRepository();
        $this->schemaRepo = new WorkflowSchemaRepository();

        SystemContainer::addItem(
            ITransitionDispatcherRepository::class,
            TransitionDispatcherRepository::class
        );
        SystemContainer::addItem(
            IWorkflowSchemaRepository::class,
            WorkflowSchemaRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([TransitionDispatcher::FIELD__NAME => 'test']);
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
    public function testSchemaHasNoDispatchers()
    {
        $operation = new BeforeSchemaCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowSchema::FIELD__NAME => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test'
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
    public function testSchemaHasDispatchers()
    {
        $operation = new BeforeSchemaCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                WorkflowSchema::FIELD__NAME => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test',
            WorkflowSchema::FIELD__TRANSITIONS => ['test']
        ]));

        $this->transitionRepo->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__SCHEMA_NAME => 'test'
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

        $this->assertEmpty(
            $this->transitionRepo->all([TransitionDispatcher::FIELD__SCHEMA_NAME => 'test'])
        );
    }
}
