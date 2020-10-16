<?php
namespace tests\plugins\workflows\expands\transitions;

use Dotenv\Dotenv;
use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandByStateTo;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class TransitionExpandByStateToTest
 *
 * @package tests\plugins\workflows\expands\transitions
 * @author jeyroik <jeyroik@gmail.com>
 */
class TransitionExpandByStateToTest extends TestCase
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
            ['workflowStates', 'name', State::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testExpand()
    {
        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'state_to',
            State::FIELD__TITLE => 'State to'
        ]));
        $operation = new TransitionExpandByStateTo();
        $transition = new Transition([
            Transition::FIELD__STATE_TO => 'state_to'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transitions.index',
                ['x-extas-expand' => 'transition.state_to']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'transition.state_to'
            ]
        ]);

        $transition = $operation($transition, $expand);

        $this->assertEquals(
            ['state_to' => [
                'name' => 'state_to',
                'title' => 'State to'
            ]],
            $transition->__toArray(),
            'Incorrect expanding: ' . print_r($transition, true)
        );
    }
}
