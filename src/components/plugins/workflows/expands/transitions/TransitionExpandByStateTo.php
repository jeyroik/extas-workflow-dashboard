<?php
namespace extas\components\plugins\workflows\expands\transitions;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class TransitionExpandByStateTo
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\transitions
 * @author jeyroik@gmail.com
 */
class TransitionExpandByStateTo extends Plugin implements IStageExpand
{
    /**
     * @param IItem|ITransition $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $subject[$subject::FIELD__STATE_TO] = $subject->getStateTo()->__toArray();

        return $subject;
    }
}
