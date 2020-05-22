<?php
namespace tests\jsonrpc\transitions;

use Dotenv\Dotenv;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
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
use extas\components\jsonrpc\transitions\TransitionByStateFrom;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\components\workflows\entities\Entity;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\components\workflows\schemas\SchemaRepository;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response as PsrResponse;

/**
 * Class TransitionByStateFromTest
 *
 * @author jeyroik@gmail.com
 */
class TransitionByStateFromTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffExtensions;

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
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->entityTemplateRepo = new EntitySampleRepository();
        $this->transitionDispatcherRepo = new TransitionDispatcherRepository();
        $this->transitionDispatcherTemplateRepo = new TransitionDispatcherSampleRepository();
        $this->transitionRepo = new TransitionRepository();
        $this->schemaRepo = new SchemaRepository();
        $this->addReposForExt([
            ITransitionDispatcherRepository::class => TransitionDispatcherRepository::class,
            ITransitionDispatcherSampleRepository::class => TransitionDispatcherSampleRepository::class,
            IEntitySampleRepository::class => EntitySampleRepository::class,
            ITransitionRepository::class => TransitionRepository::class,
            ISchemaRepository::class => SchemaRepository::class
        ]);
        $this->createRepoExt([
            'workflowTransitionDispatcherRepository', 'workflowEntityRepository', 'workflowTransitionRepository',
            'workflowSchemaRepository'
        ]);
    }

    public function tearDown(): void
    {
        $this->entityTemplateRepo->delete([EntitySample::FIELD__NAME => 'test']);
        $this->transitionDispatcherRepo->delete([TransitionDispatcher::FIELD__NAME => 'test']);
        $this->transitionDispatcherTemplateRepo->delete([TDT::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->deleteSnuffExtensions();
    }

    /**
     * @throws
     */
    public function testUnknownSchema()
    {
        $operation = new TransitionByStateFrom([
            TransitionByStateFrom::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transition.by.state.unknown.schema'
            ),
            TransitionByStateFrom::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->assertTrue($this->isResponseHasError($operation()));
    }

    /**
     * @throws
     */
    public function testUnknownEntityTemplate()
    {
        $operation = new TransitionByStateFrom([
            TransitionByStateFrom::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transition.by.state.unknown.entity'
            ),
            TransitionByStateFrom::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->assertTrue($this->isResponseHasError($operation()));
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new TransitionByStateFrom([
            TransitionByStateFrom::FIELD__PSR_REQUEST => $this->getPsrRequest('.transition.by.state'),
            TransitionByStateFrom::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test2',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $response = $operation();

        /**
         * @var $jsonRpcResponse IResponse
         * @var $schema Schema
         */
        $jsonRpcResponse = $this->getJsonRpcResponse($response);
        $this->assertFalse(isset($jsonRpcResponse[IResponse::RESPONSE__ERROR]));
        $this->assertEquals(
            [
                IResponse::RESPONSE__ID => '2f5d0719-5b82-4280-9b3b-10f23aff226b',
                IResponse::RESPONSE__VERSION => IResponse::VERSION_CURRENT,
                IResponse::RESPONSE__RESULT => [
                    [
                        Transition::FIELD__NAME => 'test',
                        Transition::FIELD__STATE_FROM => 'from',
                        Transition::FIELD__STATE_TO => 'to'
                    ]
                ]
            ],
            $jsonRpcResponse
        );
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isResponseHasError(ResponseInterface $response):bool
    {
        $jsonRpcRequest = $this->getJsonRpcResponse($response);
        return isset($jsonRpcRequest[IResponse::RESPONSE__ERROR]);
    }
}
