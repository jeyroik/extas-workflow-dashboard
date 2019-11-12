<?php
namespace extas\components\extensions\jsonrpc;

use extas\interfaces\extensions\jsonrpc\IDataExtension;

/**
 * Trait THasData
 *
 * @property $config
 *
 * @package extas\components\extensions\jsonrpc
 * @author jeyroik@gmail.com
 */
trait THasData
{
    /**
     * @param array $request
     *
     * @return array
     */
    protected function getData(array $request): array
    {
        return $request[IDataExtension::FIELD__DATA] ?? [];
    }
}
