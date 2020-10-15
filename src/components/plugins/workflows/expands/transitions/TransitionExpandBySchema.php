<?php
namespace extas\components\plugins\workflows\expands\transitions;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class TransitionExpandBySchema
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class TransitionExpandBySchema extends Plugin implements IStageExpand
{
    /**
     * @param IItem|ITransition $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $subject['schema'] = $subject->getSchema()->__toArray();

        return $subject;
    }
}
