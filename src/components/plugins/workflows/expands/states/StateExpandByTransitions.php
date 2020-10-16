<?php
namespace extas\components\plugins\workflows\expands\states;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class StateExpandByTransitions
 *
 * @method IRepository workflowTransitions()
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\states
 * @author jeyroik@gmail.com
 */
class StateExpandByTransitions extends Plugin implements IStageExpand
{
    /**
     * @param IItem|IState $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $from = $this->workflowTransitions()->all([ITransition::FIELD__STATE_FROM => $subject->getName()]);
        $to = $this->workflowTransitions()->all([ITransition::FIELD__STATE_TO => $subject->getName()]);

        $result = [
            'from' => [],
            'to' => []
        ];

        $this->append($result, 'from', $from)
            ->append($result, 'to', $to);

        $subject['transitions'] = $result;

        return $subject;
    }

    /**
     * @param array $result
     * @param string $type
     * @param array $transitions
     * @return $this
     */
    protected function append(array &$result, string $type, array $transitions)
    {
        foreach ($transitions as $transition) {
            $result[$type][] = $transition->__toArray();
        }

        return $this;
    }
}
