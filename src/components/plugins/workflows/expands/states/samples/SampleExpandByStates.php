<?php
namespace extas\components\plugins\workflows\expands\states\samples;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\states\IStateSample;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class SampleExpandByStates
 *
 * @method IRepository workflowStates()
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\states\samples
 * @author jeyroik@gmail.com
 */
class SampleExpandByStates extends Plugin implements IStageExpand
{
    /**
     * @param IItem|IStateSample $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $states = $this->workflowStates()->all([IState::FIELD__SAMPLE_NAME => $subject->getName()]);
        $result = [];

        $this->append($result, $states);

        $subject['states'] = $result;

        return $subject;
    }

    /**
     * @param array $result
     * @param string $type
     * @param array $states
     * @return $this
     */
    protected function append(array &$result, array $states)
    {
        foreach ($states as $transition) {
            $result[] = $transition->__toArray();
        }

        return $this;
    }
}
