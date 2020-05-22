<?php
namespace tests\jsonrpc\entities;

use Dotenv\Dotenv;
use extas\interfaces\samples\parameters\ISampleParameter;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\entities\EntityRepository;
use extas\interfaces\workflows\entities\IEntityRepository;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherSampleRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSample as TDT;
use extas\components\workflows\entities\Entity;
use extas\components\jsonrpc\entities\EntityTransit;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\workflows\schemas\SchemaRepository;
use extas\interfaces\workflows\schemas\ISchemaRepository;

/**
 * Class EntityTransitTest
 *
 * @author jeyroik@gmail.com
 */
class EntityTransitTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffExtensions;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $entityRepo = null;

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

        $this->entityRepo = new EntityRepository();
        $this->transitionDispatcherRepo = new TransitionDispatcherRepository();
        $this->transitionDispatcherTemplateRepo = new TransitionDispatcherSampleRepository();
        $this->transitionRepo = new TransitionRepository();
        $this->schemaRepo = new SchemaRepository();
        $this->addReposForExt([
            ITransitionDispatcherRepository::class => TransitionDispatcherRepository::class,
            ITransitionDispatcherSampleRepository::class => TransitionDispatcherSampleRepository::class,
            IEntityRepository::class => EntityRepository::class,
            ITransitionRepository::class => TransitionRepository::class,
            ISchemaRepository::class => SchemaRepository::class
        ]);
        $this->createRepoExt([
            'workflowTransitionDispatcherRepository', 'workflowTransitionDispatcherSampleRepository',
            'workflowEntityRepository', 'workflowTransitionRepository', 'workflowSchemaRepository'
        ]);
    }

    public function tearDown(): void
    {
        $this->entityRepo->delete([Entity::FIELD__NAME => 'test']);
        $this->transitionDispatcherRepo->delete([TransitionDispatcher::FIELD__NAME => 'test']);
        $this->transitionDispatcherTemplateRepo->delete([TDT::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->deleteSnuffExtensions();
    }

    public function testUnknownSchema()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest('.entity.transit.schema.unknown'),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testUnknownTransition()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.entity.transit.transition.unknown'
            ),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->entityRepo->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testInvalidEntityState()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.entity.transit.state.invalid'
            ),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->entityRepo->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

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

        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testInvalidEntityContent()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.entity.transit.content.invalid'
            ),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $this->entityRepo->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

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

        $this->transitionDispatcherRepo->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
            TransitionDispatcher::FIELD__SAMPLE_NAME => 'test',
            TransitionDispatcher::FIELD__CLASS =>
                'extas\\components\\workflows\\transitions\\dispatchers\\EntityHasAllParams',
            TransitionDispatcher::FIELD__PARAMETERS => [
                [ISampleParameter::FIELD__NAME => 'test']
            ]
        ]));

        $this->assertTrue($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }

    public function testValid()
    {
        $operation = new EntityTransit([
            EntityTransit::FIELD__PSR_REQUEST => $this->getPsrRequest('.entity.transit.valid'),
            EntityTransit::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->entityRepo->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__CLASS => Entity::class,
            Entity::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test',
            Transition::FIELD__SAMPLE_NAME => 'test'
        ]));

        $this->assertFalse($this->isJsonRpcResponseHasError($operation(), IResponse::RESPONSE__ERROR));
    }
}
