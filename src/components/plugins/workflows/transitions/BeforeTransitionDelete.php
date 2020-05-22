<?php
namespace extas\components\plugins\workflows\transitions;

use extas\components\plugins\Plugin;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class BeforeTransitionDelete
 *
 * @method workflowTransitionRepository()
 *
 * @stage extas.workflow_transitions.delete.before
 * @package extas\components\plugins\workflows\transitions
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDelete extends Plugin
{
    /**
     * @param ITransition $transition
     * @param array $where
     */
    public function __invoke(ITransition $transition, array $where): void
    {
        $this->workflowTransitionRepository()->delete([
            ITransitionDispatcher::FIELD__TRANSITION_NAME => $transition->getName()
        ]);
    }
}
