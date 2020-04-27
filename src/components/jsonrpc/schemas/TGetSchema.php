<?php
namespace extas\components\jsonrpc\schemas;

use extas\components\SystemContainer;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;

/**
 * Trait TGetSchema
 *
 * @package extas\components\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
trait TGetSchema
{
    protected ?IRepository $schemaRepo = null;

    /**
     * @param string $name
     * @return ISchema
     * @throws \Exception
     */
    protected function getSchema(string $name): ISchema
    {
        /**
         * @var $schemaRepo ISchemaRepository
         * @var $schema ISchema
         */
        $this->schemaRepo = SystemContainer::getItem(ISchemaRepository::class);
        $schema = $this->schemaRepo->one([ISchema::FIELD__NAME => $name]);

        if (!$schema) {
            throw new \Exception('Missed schema');
        }

        return $schema;
    }

    /**
     * @param $schema
     */
    protected function updateSchema($schema)
    {
        $this->schemaRepo->update($schema);
    }
}
