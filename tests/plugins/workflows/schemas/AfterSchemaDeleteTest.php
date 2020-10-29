<?php
namespace tests\plugins\workflows\schemas;

use Dotenv\Dotenv;
use extas\components\http\TSnuffHttp;
use extas\components\operations\JsonRpcOperation;
use extas\components\plugins\workflows\schemas\AfterSchemaDelete;
use extas\components\plugins\workflows\transitions\AfterTransitionDelete;
use extas\components\plugins\workflows\transitions\AfterTransitionIndex;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\states\State;
use extas\components\workflows\transitions\dispatchers\ContextHasAllParams;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class AfterSchemaDeleteTest
 * @package tests\plugins\workflows\schemas
 */
class AfterSchemaDeleteTest extends TestCase
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
            ['workflowTransitionsDispatchers', 'name', TransitionDispatcher::class],
            ['workflowTransitions', 'name', Transition::class],
            ['workflowStates', 'name', State::class],
            ['workflowEntities', 'name', Entity::class]
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
            TransitionDispatcher::FIELD__TRANSITION_NAME => 'test_transition'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test_transition',
            Transition::FIELD__SCHEMA_NAME => 'test_schema'
        ]));
        $this->getMagicClass('workflowStates')->create(new Transition([
            State::FIELD__NAME => 'test_state',
            State::FIELD__SCHEMA_NAME => 'test_schema'
        ]));
        $this->getMagicClass('workflowEntities')->create(new Transition([
            Entity::FIELD__NAME => 'test_entity',
            Entity::FIELD__SCHEMA_NAME => 'test_schema'
        ]));

        $plugin = new AfterSchemaDelete([
            AfterTransitionIndex::FIELD__PSR_REQUEST => $this->getPsrRequest('.schema.delete'),
            AfterTransitionIndex::FIELD__PSR_RESPONSE => $this->getPsrResponse()
        ]);
        $response = $this->getPsrResponse();
        $operation = new JsonRpcOperation([
            JsonRpcOperation::FIELD__NAME => 'workflow.schema.delete'
        ]);
        $result = $plugin($operation, '', $response);

        $dispatchers = $this->getMagicClass('workflowTransitionsDispatchers')->all([]);
        $this->assertEmpty($dispatchers, 'Dispatchers are not deleted');

        $transitions = $this->getMagicClass('workflowTransitions')->all([]);
        $this->assertEmpty($transitions, 'Transitions are not deleted');

        $states = $this->getMagicClass('workflowStates')->all([]);
        $this->assertEmpty($states, 'States are not deleted');

        $entities = $this->getMagicClass('workflowEntities')->all([]);
        $this->assertEmpty($entities, 'Entity is not deleted');
    }
}
