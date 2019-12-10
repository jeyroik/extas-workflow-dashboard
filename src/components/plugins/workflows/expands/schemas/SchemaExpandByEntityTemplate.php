<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\expands\PluginExpandAbstract;
use extas\components\SystemContainer;
use extas\interfaces\expands\IExpandingBox;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplate;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\transitions\IWorkflowTransition;

/**
 * Class SchemaExpandByEntityTemplate
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaExpandByEntityTemplate extends PluginExpandAbstract
{
    /**
     * @param IExpandingBox $parent
     * @param IServerRequest $request
     * @param IServerResponse $response
     */
    protected function dispatch(IExpandingBox &$parent, IServerRequest &$request, IServerResponse &$response)
    {
        /**
         * @var $schemas
         * @var $repo IWorkflowEntityTemplateRepository
         * @var $items IWorkflowEntityTemplate[]
         */
        $schemasIndex = $parent->getData();
        $schemas = $schemasIndex['schemas'] ?? [];
        $names = [];
        foreach ($schemas as $schema) {
            $names[] = $schema[IWorkflowSchema::FIELD__ENTITY_TEMPLATE];
        }

        $repo = SystemContainer::getItem(IWorkflowEntityTemplateRepository::class);
        $items = $repo->all([IWorkflowEntityTemplate::FIELD__NAME => $names]);
        $byName = [];
        foreach ($items as $entityTemplate) {
            $byName[$entityTemplate->getName()] = $entityTemplate->__toArray();
        }

        foreach ($schemas as &$schema) {
            $template = $schema[IWorkflowSchema::FIELD__ENTITY_TEMPLATE];
            $schema[IWorkflowSchema::FIELD__ENTITY_TEMPLATE] = $byName[$template] ?? [
                IWorkflowTransition::FIELD__NAME => $template,
                IWorkflowTransition::FIELD__TITLE => 'Ошибка: Неизвестный шаблон сущности [' . $template . ']'
            ];
        }

        $parent->addToValue('schemas', $schemas);
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getExpandName(): string
    {
        return 'entity';
    }
}
