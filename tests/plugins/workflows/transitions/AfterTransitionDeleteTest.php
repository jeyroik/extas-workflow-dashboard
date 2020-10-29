<?php
namespace tests\plugins\workflows\transitions;

use Dotenv\Dotenv;
use extas\components\http\TSnuffHttp;
use extas\components\operations\JsonRpcOperation;
use extas\components\plugins\workflows\transitions\AfterTransitionDelete;
use extas\components\plugins\workflows\transitions\AfterTransitionIndex;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\workflows\transitions\dispatchers\ContextHasAllParams;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

class AfterTransitionDeleteTest extends TestCase
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
            ['workflowTransitionsDispatchers', 'name', TransitionDispatcher::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testDeleteDispatchers()
    {
        $this->getMagicClass('workflowTransitionsDispatchers')->create(new TransitionDispatcher([
            TransitionDispatcher::FIELD__NAME => 'test_dispatcher',
            TransitionDispatcher::FIELD__TYPE => TransitionDispatcher::TYPE__CONDITION,
            TransitionDispatcher::FIELD__SAMPLE_NAME => '',
            TransitionDispatcher::FIELD__CLASS => ContextHasAllParams::class,
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test_transition',
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
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test_transition',
            TransitionDispatcher::FIELD__PARAMETERS => [
                'state' => [
                    'name' => 'state',
                    'value' => 'is ok'
                ]
            ]
        ]));
        $plugin = new AfterTransitionDelete([
            AfterTransitionIndex::FIELD__PSR_REQUEST => $this->getPsrRequest('.transition.delete'),
            AfterTransitionIndex::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $response = $this->getPsrResponse();
        $operation = new JsonRpcOperation([
            JsonRpcOperation::FIELD__NAME => 'workflow.transition.delete'
        ]);
        $result = $plugin($operation, '', $response);

        $dispatchers = $this->getMagicClass('workflowTransitionsDispatchers')->all([]);
        $this->assertEmpty($dispatchers, 'Dispatchers not deleted');
    }
}
