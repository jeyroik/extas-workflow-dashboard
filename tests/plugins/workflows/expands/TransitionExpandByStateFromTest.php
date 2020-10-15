<?php
namespace tests\plugins\workflows\expands;

use Dotenv\Dotenv;
use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntity;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandByStateFrom;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class TransitionExpandByStateFromTest
 *
 * @package tests\plugins\workflows\expands
 * @author jeyroik <jeyroik@gmail.com>
 */
class TransitionExpandByStateFromTest extends TestCase
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
            State::FIELD__NAME => 'state_from',
            State::FIELD__TITLE => 'State from'
        ]));
        $operation = new TransitionExpandByStateFrom();
        $transition = new Transition([
            Transition::FIELD__STATE_FROM => 'state_from'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transitions.index',
                ['x-extas-expand' => 'transition.state_from']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'transition.state_from'
            ]
        ]);

        $transition = $operation($transition, $expand);

        $this->assertEquals(
            ['state_from' => [
                'name' => 'state_from',
                'title' => 'State from'
            ]],
            $transition->__toArray(),
            'Incorrect expanding: ' . print_r($transition, true)
        );
    }
}
