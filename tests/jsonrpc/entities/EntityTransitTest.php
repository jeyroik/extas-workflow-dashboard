<?php

use PHPUnit\Framework\TestCase;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\interfaces\workflows\entities\IEntitySampleRepository;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\entities\EntitySample;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherSampleRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSample as TDT;
use extas\interfaces\parameters\IParameter;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\jsonrpc\entities\EntityTransit;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\components\workflows\schemas\SchemaRepository;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use Slim\Http\Response as PsrResponse;

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
    protected ?IRepository $transitionDispatcherTemplateRepo = null;

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

        $this->entityTemplateRepo = new EntitySampleRepository();
        $this->transitionDispatcherRepo = new TransitionDispatcherRepository();
        $this->transitionDispatcherTemplateRepo = new TransitionDispatcherSampleRepository();
        $this->transitionRepo = new TransitionRepository();
        $this->schemaRepo = new SchemaRepository();

        SystemContainer::addItem(
            ITransitionDispatcherRepository::class,
            TransitionDispatcherRepository::class
        );
        SystemContainer::addItem(
            ITransitionDispatcherSampleRepository::class,
            TransitionDispatcherSampleRepository::class
        );
        SystemContainer::addItem(
            IEntitySampleRepository::class,
            EntitySampleRepository::class
        );
        SystemContainer::addItem(
            ITransitionRepository::class,
            TransitionRepository::class
        );
        SystemContainer::addItem(
            ISchemaRepository::class,
            SchemaRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->entityTemplateRepo->delete([EntitySample::FIELD__NAME => 'test']);
        $this->transitionDispatcherRepo->delete([TransitionDispatcher::FIELD__NAME => 'test']);
        $this->transitionDispatcherTemplateRepo->delete([TDT::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
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

    public function testUnknownSchema()
    {
        $operation = new EntityTransit();
        $serverRequest = $this->getServerRequest(['schema_name' => 'unknown']);
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

    public function testUnknownTransition()
    {
        $operation = new EntityTransit();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'unknown'
        ]);
        $serverResponse = $this->getServerResponse();

        $this->entityTemplateRepo->create(new EntitySample([
            EntitySample::FIELD__NAME => 'test',
            EntitySample::FIELD__CLASS => Entity::class
        ]));

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
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

    public function testInvalidEntityState()
    {
        $operation = new EntityTransit();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'test',
            'entity' => [
                Entity::FIELD__STATE_NAME => 'not from'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->entityTemplateRepo->create(new EntitySample([
            EntitySample::FIELD__NAME => 'test',
            EntitySample::FIELD__CLASS => Entity::class
        ]));

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to'
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

    public function testInvalidEntityContent()
    {
        $operation = new EntityTransit();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'test',
            'entity' => [
                Entity::FIELD__STATE_NAME => 'from'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->entityTemplateRepo->create(new EntitySample([
            EntitySample::FIELD__NAME => 'test',
            EntitySample::FIELD__CLASS => Entity::class
        ]));

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test',
            Schema::FIELD__TRANSITIONS_NAMES => ['test']
        ]));

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to'
        ]));

        $this->transitionDispatcherRepo->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__SAMPLE_NAME => 'test',
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
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    public function testValid()
    {
        $operation = new EntityTransit();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'test',
            'entity' => [
                Entity::FIELD__STATE_NAME => 'from',
                'test' => true
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->entityTemplateRepo->create(new EntitySample([
            EntitySample::FIELD__NAME => 'test',
            EntitySample::FIELD__CLASS => Entity::class
        ]));

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test',
            Schema::FIELD__TRANSITIONS_NAMES => ['test']
        ]));

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to'
        ]));

        $this->transitionDispatcherRepo->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__SAMPLE_NAME => 'test',
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
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());
    }
}
