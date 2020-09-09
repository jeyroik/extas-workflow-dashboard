<?php
namespace tests\workflows\misc;

use extas\components\THasContext;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherExecutor;
use extas\interfaces\workflows\transits\ITransitResult;

/**
 * Class TriggerSetStateTo
 *
 * @package tests\workflows\misc
 * @author jeyroik <jeyroik@gmail.com>
 */
class TriggerSetStateTo extends TransitionDispatcher implements ITransitionDispatcherExecutor
{
    use THasContext;

    /**
     * @param ITransitResult $result
     * @param IEntity $entityEdited
     * @return bool
     */
    public function __invoke(ITransitResult &$result, IEntity &$entityEdited): bool
    {
        $entityEdited[IEntity::FIELD__STATE_NAME] = $this->getTransition()->getStateToName();

        return true;
    }
}
