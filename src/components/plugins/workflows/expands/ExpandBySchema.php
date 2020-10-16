<?php
namespace extas\components\plugins\workflows\expands;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\schemas\IHasSchema;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class ExpandBySchema
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands
 * @author jeyroik@gmail.com
 */
class ExpandBySchema extends Plugin implements IStageExpand
{
    /**
     * @param IItem|IHasSchema $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $subject['schema'] = $subject->getSchema()->__toArray();

        return $subject;
    }
}
