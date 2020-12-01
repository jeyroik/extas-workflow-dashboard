<?php
namespace extas\components\plugins\workflows\transitions;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\plugins\Plugin;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\transits\TransitResult;
use extas\interfaces\stages\IStageJsonRpcBeforeIndexResponse;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class ApplyConditions
 *
 * @package extas\components\plugins\workflows\transitions
 * @author jeyroik <jeyroik@gmail.com>
 */
class ApplyConditions extends Plugin implements IStageJsonRpcBeforeIndexResponse
{
    use THasJsonRpcRequest;
    use THasJsonRpcResponse;

    protected IEntity $entity;
    protected EntityContext $context;
    protected bool $runConditions;

    /**
     * @param ITransition[] $items
     * @return array
     */
    public function __invoke(array $items): array
    {
        $request = $this->getJsonRpcRequest();
        $params = $request->getParams();
        $this->runConditions = $params['conditions'] ?? true;
        $this->context = new EntityContext($params['context'] ?? []);
        $this->entity = new Entity($params['entity'] ?? []);

        $valid = [];
        foreach ($items as $transition) {
            if ($this->runConditions && ($this->entity->getStateName() !== $transition->getStateFromName())) {
                continue;
            }
            $this->appendIfConditionsValid($transition, $valid);
        }

        return $valid;
    }

    /**
     * @param ITransition $transition
     * @param array $valid
     * @return bool
     */
    protected function appendIfConditionsValid(ITransition $transition, array &$valid): bool
    {
        $result = new TransitResult();
        $conditions = $transition->getConditions();

        if ($this->runConditions) {
            foreach ($conditions as $condition) {
                if (!$condition->dispatch($this->context, $result, $this->entity)) {
                    return false;
                }
            }
        }

        $valid[] = $transition;

        return true;
    }
}
