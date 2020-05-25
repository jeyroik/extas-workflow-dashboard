<?php
namespace extas\components\jsonrpc\schemas;

use extas\interfaces\workflows\schemas\ISchema;

/**
 * Trait TGetSchema
 *
 * @method workflowSchemaRepository()
 *
 * @package extas\components\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
trait TGetSchema
{
    /**
     * @param string $name
     * @return ISchema
     * @throws \Exception
     */
    protected function getSchema(string $name): ISchema
    {
        /**
         * @var $schema ISchema
         */
        $schema = $this->workflowSchemaRepository()->one([ISchema::FIELD__NAME => $name]);

        if (!$schema) {
            throw new \Exception('Missed schema');
        }

        return $schema;
    }

    /**
     * @param $schema
     */
    protected function updateSchema($schema): void
    {
        $this->workflowSchemaRepository()->update($schema);
    }
}
