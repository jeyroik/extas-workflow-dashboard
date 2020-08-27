<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\Plugin;
use extas\interfaces\expands\IExpand;
use extas\interfaces\IItem;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageExpand;
use extas\interfaces\workflows\schemas\ISchema;

/**
 * Class SchemaExpandByEntityTemplate
 *
 * @method IRepository workflowEntities()
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaExpandByEntity extends Plugin implements IStageExpand
{
    /**
     * @param IItem|ISchema $subject
     * @param IExpand $expand
     * @return IItem
     */
    public function __invoke(IItem $subject, IExpand $expand): IItem
    {
        $subject[$subject::FIELD__ENTITY_NAME] = $subject->getEntity()->__toArray();

        return $subject;
    }
}
