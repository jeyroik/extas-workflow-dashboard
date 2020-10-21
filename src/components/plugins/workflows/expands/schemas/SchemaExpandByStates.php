<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\states\IState;

/**
 * Class SchemaExpandByStates
 *
 * @method IRepository workflowStates()
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaExpandByStates extends Plugin implements IStageExpand
{
    /**
     * @param IItem|ISchema $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $states = $this->workflowStates()->all([IState::FIELD__SCHEMA_NAME => $subject->getName()]);

        foreach ($states as $index => $state) {
            $states[$index] = $state->__toArray();
        }

        $subject['states'] = $states;

        return $subject;
    }
}
