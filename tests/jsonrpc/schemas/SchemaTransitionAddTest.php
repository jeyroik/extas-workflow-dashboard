<?php
namespace tests;

use extas\components\workflows\transitions\TransitionSample;
use extas\components\workflows\transitions\TransitionSampleRepository;
use extas\interfaces\workflows\transitions\ITransitionSampleRepository;
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
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\components\jsonrpc\schemas\SchemaTransitionAdd;
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
 * Class SchemaTransitionAddTest
 *
 * @author jeyroik@gmail.com
 */
class SchemaTransitionAddTest extends TestCase
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
    protected ?IRepository $transitionSampleRepo = null;

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
        $this->transitionSampleRepo = new TransitionSampleRepository();
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
            ITransitionSampleRepository::class,
            TransitionSampleRepository::class
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
        $this->transitionSampleRepo->delete([TransitionSample::FIELD__NAME => 'test']);
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

    /**
     * @throws
     */
    public function testUnknownSchema()
    {
        $operation = new SchemaTransitionAdd();
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

    /**
     * @throws
     */
    public function testUnknownTransition()
    {
        $operation = new SchemaTransitionAdd();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'unknown'
        ]);
        $serverResponse = $this->getServerResponse();

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

    /**
     * @throws
     */
    public function testSchemaHasNotTransition()
    {
        $operation = new SchemaTransitionAdd();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'test',
            'transition_sample_name' => 'test'
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test',
            Schema::FIELD__TRANSITIONS_NAMES => []
        ]));

        $this->transitionSampleRepo->create(new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $operation($serverRequest, $serverResponse);

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());
    }

    /**
     * @throws
     */
    public function testTransitionWithDispatchers()
    {
        $operation = new SchemaTransitionAdd();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'test',
            'transition_sample_name' => 'test',
            'dispatchers' => [
                [
                    ITransitionDispatcher::FIELD__NAME => 'test',
                    ITransitionDispatcher::FIELD__TYPE => ITransitionDispatcher::TYPE__CONDITION,
                    ITransitionDispatcher::FIELD__SAMPLE_NAME => 'test',
                    ITransitionDispatcher::FIELD__PARAMETERS => [
                        [
                            IParameter::FIELD__NAME => 'field_name',
                            IParameter::FIELD__VALUE => 'test'
                        ]
                    ]
                ]
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test',
            Schema::FIELD__TRANSITIONS_NAMES => []
        ]));

        $this->transitionSampleRepo->create(new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $operation($serverRequest, $serverResponse);

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());

        $dispatcher = $this->transitionDispatcherRepo->one([ITransitionDispatcher::FIELD__NAME => 'test']);
        $this->assertNotEmpty($dispatcher);
    }

    /**
     * @throws
     */
    public function testTransitionWithDispatchersWithUnknownTemplate()
    {
        $operation = new SchemaTransitionAdd();
        $serverRequest = $this->getServerRequest([
            'schema_name' => 'test',
            'transition_name' => 'test',
            'transition_sample_name' => 'test',
            'dispatchers' => [
                [
                    ITransitionDispatcher::FIELD__NAME => 'test',
                    ITransitionDispatcher::FIELD__TYPE => ITransitionDispatcher::TYPE__CONDITION,
                    ITransitionDispatcher::FIELD__SAMPLE_NAME => 'unknown',
                    ITransitionDispatcher::FIELD__PARAMETERS => [
                        [
                            IParameter::FIELD__NAME => 'field_name',
                            IParameter::FIELD__VALUE => 'test'
                        ]
                    ]
                ]
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test',
            Schema::FIELD__TRANSITIONS_NAMES => []
        ]));

        $this->transitionSampleRepo->create(new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $operation($serverRequest, $serverResponse);

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());

        $dispatcher = $this->transitionDispatcherRepo->one([ITransitionDispatcher::FIELD__NAME => 'test']);
        $this->assertEmpty($dispatcher);
    }
}
