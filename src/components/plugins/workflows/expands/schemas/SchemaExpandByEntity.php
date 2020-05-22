<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\expands\PluginExpandAbstract;
use extas\components\SystemContainer;
use extas\interfaces\expands\IExpandingBox;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\entities\IEntityRepository;
use extas\interfaces\workflows\schemas\ISchema;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SchemaExpandByEntityTemplate
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaExpandByEntity extends PluginExpandAbstract
{
    /**
     * @param IExpandingBox $parent
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    protected function dispatch(IExpandingBox &$parent, RequestInterface $request, ResponseInterface $response)
    {
        /**
         * @var $schemas
         * @var $repo IEntityRepository
         * @var $items IEntity[]
         */
        $value = $parent->getValue([]);
        if (empty($value)) {
            $schemasIndex = $parent->getData();
            $schemas = $schemasIndex['schemas'] ?? [];
        } else {
            $schemas = $value['schemas'];
        }

        $names = array_column($schemas, ISchema::FIELD__ENTITY_NAME);
        $repo = SystemContainer::getItem(IEntityRepository::class);
        $items = $repo->all([IEntity::FIELD__NAME => $names]);
        $byName = [];
        foreach ($items as $entity) {
            $byName[$entity->getName()] = $entity->__toArray();
        }

        foreach ($schemas as &$schema) {
            $entityName = $schema[ISchema::FIELD__ENTITY_NAME];
            $schema[ISchema::FIELD__ENTITY_NAME] = $byName[$entityName] ?? [
                IEntity::FIELD__NAME => $entityName,
                IEntity::FIELD__TITLE => 'Ошибка: Неизвестная сущность [' . $entityName . ']'
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
