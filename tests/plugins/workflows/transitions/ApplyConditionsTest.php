<?php
namespace tests\plugins\workflows\transitions;

use Dotenv\Dotenv;
use extas\components\http\TSnuffHttp;
use extas\components\operations\JsonRpcOperation;
use extas\components\plugins\workflows\transitions\ApplyConditions;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\workflows\transitions\dispatchers\ContextHasAllParams;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class ApplyConditionsTest
 *
 * @package tests\plugins\workflows\transitions
 * @author jeyroik <jeyroik@gmail.com>
 */
class ApplyConditionsTest extends TestCase
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
            ['workflowTransitionsDispatchers', 'name', TransitionDispatcher::class],
            ['workflowStates', 'name', State::class],
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testTransitionsValidating()
    {
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test_transition_1'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test_transition_2'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test_transition_3'
        ]));
        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test_dispatcher',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__SAMPLE_NAME => '',
            TransitionDispatcher::FIELD__CLASS => ContextHasAllParams::class,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test_transition_2',
            TransitionDispatcher::FIELD__PARAMETERS => [
                'test' => [
                    'name' => 'test',
                    'value' => 'is ok'
                ]
            ]
        ]));
        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test_dispatcher',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__SAMPLE_NAME => '',
            TransitionDispatcher::FIELD__CLASS => ContextHasAllParams::class,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test_transition_3',
            TransitionDispatcher::FIELD__PARAMETERS => [
                'state' => [
                    'name' => 'state',
                    'value' => 'is ok'
                ]
            ]
        ]));
        $plugin = new ApplyConditions([
            ApplyConditions::FIELD__PSR_REQUEST => $this->getPsrRequest('.transitions.index'),
            ApplyConditions::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $response = $this->getPsrResponse();
        $response->getBody()->write(json_encode([
            'result' => [
                'items' => [
                    [
                        'name' => 'test_transition_1'
                    ],
                    [
                        'name' => 'test_transition_2'
                    ],
                    [
                        'name' => 'test_transition_3'
                    ]
                ],
                'total' => 3
            ],
            'id' => '2f5d0719-5b82-4280-9b3b-10f23aff226b',
            'jsonrpc' => '2.0'
        ]));
        $operation = new JsonRpcOperation([
            JsonRpcOperation::FIELD__NAME => 'workflow.transition.index'
        ]);
        $result = $plugin([
            new Transition([
                Transition::FIELD__NAME => 'test_transition_1',
                Transition::FIELD__STATE_FROM => 'test'
            ]),
            new Transition([
                Transition::FIELD__NAME => 'test_transition_2',
                Transition::FIELD__STATE_FROM => 'test'
            ]),
            new Transition([
                Transition::FIELD__NAME => 'test_transition_3',
                Transition::FIELD__STATE_FROM => 'test2'
            ])
        ]);

        $this->assertCount(1, $result);
    }
}
