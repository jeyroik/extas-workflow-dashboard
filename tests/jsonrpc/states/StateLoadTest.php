<?php
namespace tests\jsonrpc\states;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use extas\components\http\TSnuffHttp;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\states\State;
use extas\components\jsonrpc\states\StateLoad;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\states\IStateRepository;
use extas\components\workflows\states\StateRepository;

/**
 * Class StateLoadTest
 *
 * @author jeyroik@gmail.com
 */
class StateLoadTest extends TestCase
{
    use TSnuffHttp;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->stateRepo = new StateRepository();

        SystemContainer::addItem(
            IStateRepository::class,
            StateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->stateRepo->delete([State::FIELD__TITLE => 'test']);
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new StateLoad([
            StateLoad::FIELD__PSR_REQUEST => $this->getPsrRequest('.state.load'),
            StateLoad::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test2',
            State::FIELD__TITLE => 'test'
        ]));

        $response = $operation();

        /**
         * @var $jsonRpcResponse IResponse
         */
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
