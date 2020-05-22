<?php
namespace tests\jsonrpc\transitions;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\jsonrpc\transitions\TransitionLoad;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\states\IStateRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;

/**
 * Class TransitionLoadTest
 *
 * @author jeyroik@gmail.com
 */
class TransitionLoadTest extends TestCase
{
    use TSnuffExtensions;
    use TSnuffHttp;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $repo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->repo = new TransitionRepository();
        $this->stateRepo = new StateRepository();
        $this->addReposForExt([
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowStateRepository' => StateRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->repo->delete([Transition::FIELD__TITLE => 'test']);
        $this->stateRepo->delete([State::FIELD__TITLE => 'test']);
        $this->deleteSnuffExtensions();
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new TransitionLoad([
            TransitionLoad::FIELD__PSR_REQUEST => $this->getPsrRequest('.transition.load'),
            TransitionLoad::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->repo->create(new Transition([
            Transition::FIELD__NAME => 'already-exists',
            Transition::FIELD__TITLE => 'test'
        ]));

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'from',
            State::FIELD__TITLE => 'test'
        ]));
        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'to',
            State::FIELD__TITLE => 'test'
        ]));

        $response = $operation();
        $jsonRpcResponse = $this->getJsonRpcResponse($response);
        $this->assertFalse(isset($jsonRpcResponse[IResponse::RESPONSE__ERROR]));
        $this->assertEquals(
            [
                IResponse::RESPONSE__ID => '2f5d0719-5b82-4280-9b3b-10f23aff226b',
                IResponse::RESPONSE__VERSION => IResponse::VERSION_CURRENT,
                IResponse::RESPONSE__RESULT => [
                    'created_count' => 1,
                    'got_count' => 2
                ]
            ],
            $jsonRpcResponse
        );
    }
}
