<?php
namespace extas\components\plugins\workflows\expands\transitions;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class TransitionExpandByDispatchers
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\transitions
 * @author jeyroik@gmail.com
 */
class TransitionExpandByDispatchers extends Plugin implements IStageExpand
{
    /**
     * @param IItem|ITransition $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $result = [
            'conditions' => [],
            'validators' => [],
            'triggers' => []
        ];

        $this->append($result, 'conditions', $subject->getConditions())
            ->append($result, 'validators', $subject->getValidators())
            ->append($result, 'triggers', $subject->getTriggers());

        $subject['dispatchers'] = $result;

        return $subject;
    }

    /**
     * @param array $result
     * @param string $type
     * @param array $dispatchers
     * @return $this
     */
    protected function append(array &$result, string $type, array $dispatchers)
    {
        foreach ($dispatchers as $dispatcher) {
            $result[$type][] = $dispatcher->__toArray();
        }

        return $this;
    }
}
