<?php
namespace extas\components\jsonrpc\schemas;

use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\schemas\ISchema;

/**
 * Trait TGetSchema
 *
 * @method IRepository workflowSchemas()
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
        $schema = $this->workflowSchemas()->one([ISchema::FIELD__NAME => $name]);

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
        $this->workflowSchemas()->update($schema);
    }
}
