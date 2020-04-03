<?php

use PHPUnit\Framework\TestCase;
use extas\components\workflows\entities\WorkflowEntityTemplateRepository;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\workflows\entities\WorkflowEntityTemplate;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplateRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplate as TDT;
use extas\interfaces\parameters\IParameter;
use extas\components\workflows\entities\WorkflowEntity;
use extas\components\workflows\entities\WorkflowEntityContext;
use extas\components\jsonrpc\entities\EntityTransit;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\components\workflows\schemas\WorkflowSchemaRepository;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;

/**
 * Class EntityTransitTest
 *
 * @author jeyroik@gmail.com
 */
class EntityTransitTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $entityTemplateRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionDispatcherRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionTemplateDispatcherRepo = null;

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

        $this->entityTemplateRepo = new WorkflowEntityTemplateRepository();
        $this->transitionDispatcherRepo = new TransitionDispatcherRepository();
        $this->transitionDispatcherTemplateRepo = new TransitionDispatcherTemplateRepository();
        $this->transitionRepo = new WorkflowTransitionRepository();
        $this->schemaRepo = new WorkflowSchemaRepository();

        SystemContainer::addItem(
            ITransitionDispatcherRepository::class,
            TransitionDispatcherRepository::class
        );
        SystemContainer::addItem(
            ITransitionDispatcherTemplateRepository::class,
            TransitionDispatcherTemplateRepository::class
        );
        SystemContainer::addItem(
            IWorkflowEntityTemplateRepository::class,
            WorkflowEntityTemplateRepository::class
        );
        SystemContainer::addItem(
            IWorkflowTransitionRepository::class,
            WorkflowTransitionRepository::class
        );
        SystemContainer::addItem(
            IWorkflowSchemaRepository::class,
            WorkflowSchemaRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->entityTemplateRepo->delete([WorkflowEntityTemplate::FIELD__NAME => 'test']);
        $this->transitionDispatcherRepo->delete([TransitionDispatcher::FIELD__NAME => 'test']);
        $this->transitionDispatcherTemplateRepo->delete([TDT::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
        $this->schemaRepo->delete([WorkflowSchema::FIELD__NAME => 'test']);
    }

    public function testUnknownSchema()
    {
        $operation = new EntityTransit();
        $serverRequest = new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                IRequest::SUBJECT => new Request([
                    IRequest::FIELD__PARAMS => [
                        'schema_name' => 'unknown'
                    ]
                ])
            ]
        ]);
        $serverResponse = new ServerResponse([
            ServerResponse::FIELD__PARAMETERS => [
                IResponse::SUBJECT => new Response()
            ]
        ]);
        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT);
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    public function testUnknownTransition()
    {
        $operation = new EntityTransit();
        $serverRequest = new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                IRequest::SUBJECT => new Request([
                    IRequest::FIELD__PARAMS => [
                        'schema_name' => 'test',
                        'transition_name' => 'unknown'
                    ]
                ])
            ]
        ]);
        $serverResponse = new ServerResponse([
            ServerResponse::FIELD__PARAMETERS => [
                IResponse::SUBJECT => new Response()
            ]
        ]);

        $this->entityTemplateRepo->create(new WorkflowEntityTemplate([
            WorkflowEntityTemplate::FIELD__NAME => 'test',
            WorkflowEntityTemplate::FIELD__CLASS => WorkflowEntityContext::class
        ]));

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT);
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    public function testInvalidEntityState()
    {
        $operation = new EntityTransit();
        $serverRequest = new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                IRequest::SUBJECT => new Request([
                    IRequest::FIELD__PARAMS => [
                        'schema_name' => 'test',
                        'transition_name' => 'test',
                        'entity' => [
                            WorkflowEntity::FIELD__STATE => 'not from'
                        ]
                    ]
                ])
            ]
        ]);
        $serverResponse = new ServerResponse([
            ServerResponse::FIELD__PARAMETERS => [
                IResponse::SUBJECT => new Response()
            ]
        ]);

        $this->entityTemplateRepo->create(new WorkflowEntityTemplate([
            WorkflowEntityTemplate::FIELD__NAME => 'test',
            WorkflowEntityTemplate::FIELD__CLASS => WorkflowEntityContext::class
        ]));

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test'
        ]));

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__STATE_FROM => 'from',
            WorkflowTransition::FIELD__STATE_TO => 'to'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT);
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    public function testInvalidEntityContent()
    {
        $operation = new EntityTransit();
        $serverRequest = new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                IRequest::SUBJECT => new Request([
                    IRequest::FIELD__PARAMS => [
                        'schema_name' => 'test',
                        'transition_name' => 'test',
                        'entity' => [
                            WorkflowEntity::FIELD__STATE => 'from'
                        ]
                    ]
                ])
            ]
        ]);
        $serverResponse = new ServerResponse([
            ServerResponse::FIELD__PARAMETERS => [
                IResponse::SUBJECT => new Response()
            ]
        ]);

        $this->entityTemplateRepo->create(new WorkflowEntityTemplate([
            WorkflowEntityTemplate::FIELD__NAME => 'test',
            WorkflowEntityTemplate::FIELD__CLASS => WorkflowEntityContext::class
        ]));

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test'
        ]));

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__STATE_FROM => 'from',
            WorkflowTransition::FIELD__STATE_TO => 'to'
        ]));

        $this->transitionDispatcherRepo->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__TEMPLATE => 'test',
            TransitionDispatcher::FIELD__PARAMETERS => [
                [IParameter::FIELD__NAME => 'test']
            ]
        ]));

        $this->transitionDispatcherTemplateRepo->create(new TDT([
            TDT::FIELD__NAME => 'test',
            TDT::FIELD__TITLE => '',
            TDT::FIELD__DESCRIPTION => '',
            TDT::FIELD__CLASS => 'extas\\components\\workflows\\transitions\\dispatchers\\EntityHasAllParams',
            TDT::FIELD__PARAMETERS => []
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT);
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    public function testValid()
    {
        $operation = new EntityTransit();
        $serverRequest = new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                IRequest::SUBJECT => new Request([
                    IRequest::FIELD__PARAMS => [
                        'schema_name' => 'test',
                        'transition_name' => 'test',
                        'entity' => [
                            WorkflowEntity::FIELD__STATE => 'from',
                            'test' => true
                        ]
                    ]
                ])
            ]
        ]);
        $serverResponse = new ServerResponse([
            ServerResponse::FIELD__PARAMETERS => [
                IResponse::SUBJECT => new Response()
            ]
        ]);

        $this->entityTemplateRepo->create(new WorkflowEntityTemplate([
            WorkflowEntityTemplate::FIELD__NAME => 'test',
            WorkflowEntityTemplate::FIELD__CLASS => WorkflowEntityContext::class
        ]));

        $this->schemaRepo->create(new WorkflowSchema([
            WorkflowSchema::FIELD__NAME => 'test',
            WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test'
        ]));

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__STATE_FROM => 'from',
            WorkflowTransition::FIELD__STATE_TO => 'to'
        ]));

        $this->transitionDispatcherRepo->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__SCHEMA_NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__TEMPLATE => 'test',
            TransitionDispatcher::FIELD__PARAMETERS => [
                [IParameter::FIELD__NAME => 'test']
            ]
        ]));

        $this->transitionDispatcherTemplateRepo->create(new TDT([
            TDT::FIELD__NAME => 'test',
            TDT::FIELD__TITLE => '',
            TDT::FIELD__DESCRIPTION => '',
            TDT::FIELD__CLASS => 'extas\\components\\workflows\\transitions\\dispatchers\\EntityHasAllParams',
            TDT::FIELD__PARAMETERS => []
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT);
        $this->assertTrue($jsonRpcResponse->hasError());
    }
}
