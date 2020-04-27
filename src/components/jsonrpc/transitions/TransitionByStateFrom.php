<?php
namespace extas\components\jsonrpc\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\jsonrpc\schemas\TGetSchema;
use extas\components\SystemContainer;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\transits\TransitResult;
use extas\interfaces\IItem;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

/**
 * Class TransitionByStateFrom
 *
 * @stage run.jsonrpc.transition.by_state_from.index
 * @package extas\components\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class TransitionByStateFrom extends OperationDispatcher
{
    use TGetSchema;

    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $jRpcData = $request->getParams();

        try {
            $schema = $this->getSchema($jRpcData['schema_name'] ?? '');
            $state = $jRpcData['state_name'];
            $entity = new Entity($jRpcData['entity'] ?? []);
            $transitions = $this->getTransitions($state, $schema);
            $result = [];
            $context = new EntityContext($jRpcData['context'] ?? []);

            foreach ($transitions as $transition) {
                $this->addValid($transition, $entity, $context, $result);
            }

            $response->success(array_values($result));
        } catch (\Exception $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    /**
     * @param ITransition $transition
     * @param IEntity $entity
     * @param IItem $context
     * @param array $result
     */
    protected function addValid($transition, $entity, $context, array &$result): void
    {
        $transitResult = new TransitResult();
        $conditions = $transition->getConditions();
        foreach ($conditions as $condition) {
            if ($condition->dispatch($context, $transitResult, $entity)) {
                $result[$transition->getName()] = $transition->__toArray();
            }
        }
    }

    /**
     * @param string $stateName
     * @param ISchema $schema
     *
     * @return array|ITransition[]
     */
    protected function getTransitions(string $stateName, ISchema $schema): array
    {
        /**
         * @var $repo ITransitionRepository
         * @var $transitions ITransition[]
         */
        $repo = SystemContainer::getItem(ITransitionRepository::class);

        return $repo->all([
            ITransition::FIELD__NAME => $schema->getTransitionsNames(),
            ITransition::FIELD__STATE_FROM => $stateName
        ]);
    }
}
