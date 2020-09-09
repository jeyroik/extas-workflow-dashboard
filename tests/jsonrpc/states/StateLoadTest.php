<?php
namespace tests\jsonrpc\states;

use extas\components\repositories\TSnuffRepository;
use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\jsonrpc\states\StateLoad;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\workflows\states\StateRepository;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class StateLoadTest
 *
 * @author jeyroik@gmail.com
 */
class StateLoadTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['workflowStates', 'name', State::class],
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new StateLoad();

        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'test2',
            State::FIELD__TITLE => 'test'
        ]));

        $result = $operation([
            StateLoad::FIELD__PSR_REQUEST => $this->getPsrRequest('.state.load'),
            StateLoad::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);

        $this->assertEquals(
            [
                'created_count' => 1,
                'got_count' => 2
            ],
            $result,
            'Incorrect result: ' . print_r($result, true)
        );
    }
}
