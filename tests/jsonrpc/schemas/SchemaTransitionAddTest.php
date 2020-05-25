<?php
namespace tests\jsonrpc\schemas;

use Dotenv\Dotenv;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\transitions\TransitionSample;
use extas\components\workflows\transitions\TransitionSampleRepository;
use PHPUnit\Framework\TestCase;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\entities\EntitySample;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSample as TDT;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\components\jsonrpc\schemas\SchemaTransitionAdd;
use extas\components\workflows\schemas\SchemaRepository;


/**
 * Class SchemaTransitionAddTest
 *
 * @author jeyroik@gmail.com
 */
class SchemaTransitionAddTest extends TestCase
{
    use TSnuffExtensions;
    use TSnuffHttp;

    protected ?IRepository $entityTemplateRepo = null;
    protected ?IRepository $transitionDispatcherRepo = null;
    protected ?IRepository $transitionDispatcherTemplateRepo = null;
    protected ?IRepository $transitionRepo = null;
    protected ?IRepository $transitionSampleRepo = null;

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
        $this->transitionSampleRepo = new TransitionSampleRepository();
        $this->schemaRepo = new SchemaRepository();
        $this->addReposForExt([
            'workflowTransitionDispatcherRepository' => TransitionDispatcherRepository::class,
            'workflowTransitionDispatcherSampleRepository' => TransitionDispatcherSampleRepository::class,
            'workflowEntitySampleRepository' => EntitySampleRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowTransitionSampleRepository' => TransitionSampleRepository::class,
            'workflowSchemaRepository' => SchemaRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->entityTemplateRepo->delete([EntitySample::FIELD__NAME => 'test']);
        $this->transitionDispatcherRepo->delete([TransitionDispatcher::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__SAMPLE_NAME => 'test']);
        $this->transitionSampleRepo->delete([TransitionSample::FIELD__NAME => 'test']);
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->deleteSnuffExtensions();
    }

    /**
     * @throws
     */
    public function testUnknownSchema()
    {
        $operation = new SchemaTransitionAdd([
            SchemaTransitionAdd::FIELD__PSR_REQUEST => $this->getPsrRequest('.trad.missed.schema'),
            SchemaTransitionAdd::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $response = $operation();

        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    /**
     * @throws
     */
    public function testSchemaHasTransition()
    {
        $operation = new SchemaTransitionAdd([
            SchemaTransitionAdd::FIELD__PSR_REQUEST => $this->getPsrRequest('.trad.has.transition'),
            SchemaTransitionAdd::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createSchema();

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__SAMPLE_NAME => 'test',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $this->transitionSampleRepo->create(new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $response = $operation();
        $this->assertTrue(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );
    }

    /**
     * @throws
     */
    public function testTransitionWithDispatchers()
    {
        $operation = new SchemaTransitionAdd([
            SchemaTransitionAdd::FIELD__PSR_REQUEST => $this->getPsrRequest('.trad.with.dispatchers'),
            SchemaTransitionAdd::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->createSchema();

        $this->transitionSampleRepo->create(new TransitionSample([
            TransitionSample::FIELD__NAME => 'test',
            TransitionSample::FIELD__STATE_FROM => 'from',
            TransitionSample::FIELD__STATE_TO => 'to'
        ]));

        $response = $operation();
        $this->assertFalse(
            $this->isJsonRpcResponseHasError($response),
            print_r($this->getJsonRpcResponse($response), true)
        );

        $dispatcher = $this->transitionDispatcherRepo->one([ITransitionDispatcher::FIELD__NAME => 'test']);
        $this->assertNotEmpty($dispatcher, 'Dispatcher is not removed');
    }

    protected function createSchema(): void
    {
        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
    }
}
