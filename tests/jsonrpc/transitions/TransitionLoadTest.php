<?php
namespace tests\jsonrpc\transitions;

use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\transitions\Transition;
use extas\components\jsonrpc\transitions\TransitionLoad;
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
    use TSnuffRepositoryDynamic;
    use THasMagicClass;
    use TSnuffHttp;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['workflowTransitions', 'name', Transition::class],
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
        $operation = new TransitionLoad();

        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'already-exists',
            Transition::FIELD__TITLE => 'test'
        ]));

        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'from',
            State::FIELD__TITLE => 'test'
        ]));
        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'to',
            State::FIELD__TITLE => 'test'
        ]));

        $result = $operation([
            TransitionLoad::FIELD__PSR_REQUEST => $this->getPsrRequest('.transition.load'),
            TransitionLoad::FIELD__PSR_RESPONSE => $this->getPsrResponse()
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
