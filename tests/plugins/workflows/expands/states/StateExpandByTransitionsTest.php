<?php
namespace tests\plugins\workflows\expands\states;

use Dotenv\Dotenv;
use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\states\StateExpandByTransitions;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandByStateFrom;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class StateExpandByTransitionsTest
 *
 * @package tests\plugins\workflows\expands\states
 * @author jeyroik <jeyroik@gmail.com>
 */
class StateExpandByTransitionsTest extends TestCase
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
            ['workflowTransitions', 'name', Transition::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testExpand()
    {
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__STATE_FROM => 'test',
            Transition::FIELD__STATE_TO => 'to'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'test'
        ]));
        $operation = new StateExpandByTransitions();
        $state = new State([
            State::FIELD__NAME => 'test'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.state.index',
                ['x-extas-expand' => 'state.transitions']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'state.transitions'
            ]
        ]);

        $state = $operation($state, $expand);

        $this->assertEquals(
            [
                'name' => 'test',
                'transitions' => [
                    'from' => [
                        [
                            'state_from' => 'test',
                            'state_to' => 'to'
                        ]
                    ],
                    'to' => [
                        [
                            'state_from' => 'from',
                            'state_to' => 'test'
                        ]
                    ]
                ]
            ],
            $state->__toArray(),
            'Incorrect expanding: ' . print_r($state->__toArray(), true)
        );
    }
}
