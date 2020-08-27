<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\workflows\exceptions\transitions\ExceptionTransitionMissed;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Trait TGetTransition
 *
 * @method IRepository workflowTransitions()
 *
 * @package extas\components\jsonrpc\workflows
 * @author jeyroik@gmail.com
 */
trait TGetTransition
{
    /**
     * @param array $query
     * @param string $transitionName
     * @return ITransition|null
     * @throws
     */
    protected function getTransition(array $query, string $transitionName): ?ITransition
    {
        $transition = $this->workflowTransitions()->one($query);

        if (!$transition) {
            throw new ExceptionTransitionMissed($transitionName);
        }

        return $transition;
    }
}
