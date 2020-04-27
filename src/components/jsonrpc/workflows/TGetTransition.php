<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\SystemContainer;
use extas\components\workflows\exceptions\transitions\ExceptionTransitionMissed;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

/**
 * Trait TGetTransition
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
        /**
         * @var ITransitionRepository $transitionRepo
         */
        $transitionRepo = SystemContainer::getItem(ITransitionRepository::class);
        $transition = $transitionRepo->one($query);

        if (!$transition) {
            throw new ExceptionTransitionMissed($transitionName);
        }

        return $transition;
    }
}
