<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class SchemaExpandByTransitions
 *
 * @method IRepository workflowTransitions()
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaExpandByTransitions extends Plugin implements IStageExpand
{
    /**
     * @param IItem|ISchema $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $transitions = $this->workflowTransitions()->all([
            ITransition::FIELD__SCHEMA_NAME => $subject->getName()
        ]);

        foreach ($transitions as $index => $transition) {
            $transitions[$index] = $transition->__toArray();
        }

        $subject['transitions'] = $transitions;

        return $subject;
    }
}
