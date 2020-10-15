<?php
namespace tests\plugins\workflows\expands;

use Dotenv\Dotenv;
use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntity;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandByDispatchers;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandBySchema;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandByStateFrom;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandByStateTo;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class TransitionExpandByDispatchersTest
 *
 * @package tests\plugins\workflows\expands
 * @author jeyroik <jeyroik@gmail.com>
 */
class TransitionExpandByDispatchersTest extends TestCase
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
            ['workflowTransitionsDispatchers', 'name', TransitionDispatcher::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testExpand()
    {
        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'condition',
            TransitionDispatcher::FIELD__TITLE => 'Condition',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test'
        ]));
        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'validator',
            TransitionDispatcher::FIELD__TITLE => 'Validator',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__VALIDATOR,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test'
        ]));
        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'trigger',
            TransitionDispatcher::FIELD__TITLE => 'Trigger',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__TRIGGER,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test'
        ]));
        $operation = new TransitionExpandByDispatchers();
        $transition = new Transition([
            Transition::FIELD__NAME => 'test'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transitions.index',
                ['x-extas-expand' => 'transition.dispatchers']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'transition.dispatchers'
            ]
        ]);

        $transition = $operation($transition, $expand);

        $this->assertEquals(
            [
                'name' => 'test',
                'dispatchers' => [
                    'conditions' => [
                        [
                            'name' => 'condition',
                            'title' => 'Condition',
                            'type' => 'condition',
                            'transition_name' => 'test'
                        ]
                    ],
                    'validators' => [
                        [
                            'name' => 'validator',
                            'title' => 'Validator',
                            'type' => 'validator',
                            'transition_name' => 'test'
                        ]
                    ],
                    'triggers' => [
                        [
                            'name' => 'trigger',
                            'title' => 'Trigger',
                            'type' => 'trigger',
                            'transition_name' => 'test'
                        ]
                    ]
                ]
            ],
            $transition->__toArray(),
            'Incorrect expanding: ' . print_r($transition->__toArray(), true)
        );
    }
}
