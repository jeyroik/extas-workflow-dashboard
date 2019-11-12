<?php
namespace extas\interfaces\extensions\jsonrpc;

/**
 * Interface IDataExtension
 *
 * @package extas\interfaces\extensions\jsonrpc
 * @author jeyroik@gmail.com
 */
interface IDataExtension
{
    const FIELD__DATA = 'data';

    /**
     * @param array $request
     *
     * @return array
     */
    public function getData(array $request): array;
}
