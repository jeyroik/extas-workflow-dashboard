<?php
namespace tests\jsonrpc\transitions;

use extas\interfaces\jsonrpc\IResponse;

use extas\components\repositories\TSnuffRepository;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\transitions\Transition;
use extas\components\jsonrpc\transitions\TransitionLoad;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class TransitionLoadTest
 *
 * @author jeyroik@gmail.com
 */
class TransitionLoadTest extends TestCase
{
    use TSnuffRepository;
    use TSnuffHttp;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->registerSnuffRepos([
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowStateRepository' => StateRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
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

        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'already-exists',
            Transition::FIELD__TITLE => 'test'
        ]));

        $this->createWithSnuffRepo('workflowStateRepository', new State([
            State::FIELD__NAME => 'from',
            State::FIELD__TITLE => 'test'
        ]));
        $this->createWithSnuffRepo('workflowStateRepository', new State([
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
