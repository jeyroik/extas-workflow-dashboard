<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\expands\PluginExpandAbstract;
use extas\components\SystemContainer;
use extas\interfaces\expands\IExpandingBox;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;
use extas\interfaces\workflows\entities\IEntitySample;
use extas\interfaces\workflows\entities\IEntitySampleRepository;
use extas\interfaces\workflows\schemas\ISchema;

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
         * @var $repo IEntitySampleRepository
         * @var $items IEntitySample[]
         */
        $value = $parent->getValue([]);
        if (empty($value)) {
            $schemasIndex = $parent->getData();
            $schemas = $schemasIndex['schemas'] ?? [];
        } else {
            $schemas = $value['schemas'];
        }
        $names = [];
        foreach ($schemas as $schema) {
            $names[] = $schema[ISchema::FIELD__ENTITY_NAME];
        }

        $repo = SystemContainer::getItem(IEntitySampleRepository::class);
        $items = $repo->all([IEntitySample::FIELD__NAME => $names]);
        $byName = [];
        foreach ($items as $entityTemplate) {
            $byName[$entityTemplate->getName()] = $entityTemplate->__toArray();
        }

        foreach ($schemas as &$schema) {
            $template = $schema[ISchema::FIELD__ENTITY_NAME];
            $schema[ISchema::FIELD__ENTITY_NAME] = $byName[$template] ?? [
                IEntitySample::FIELD__NAME => $template,
                IEntitySample::FIELD__TITLE => 'Ошибка: Неизвестный шаблон сущности [' . $template . ']'
            ];
        }

        $parent->addToValue('schemas', $schemas);
    }

    /**
     * @return string
     */
    protected function getExpandName(): string
    {
        return 'entity';
    }
}
