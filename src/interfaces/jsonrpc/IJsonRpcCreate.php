<?php
namespace extas\interfaces\jsonrpc;

/**
 * Interface IJsonRpcCreate
 *
 * @package extas\interfaces\jsonrpc
 * @author jeyroik@gmail.com
 */
interface IJsonRpcCreate extends IJsonRpcIndex
{
    const FIELD__ITEM_CLASS = 'item_class';
    const FIELD__ITEM_DATA = 'item_data';

    /**
     * @return string
     */
    public function getItemClass(): string;

    /**
     * @return array
     */
    public function getItemData(): array;

    /**
     * @param string $className
     *
     * @return IJsonRpcCreate
     */
    public function setItemClass(string $className): IJsonRpcCreate;

    /**
     * @param array $data
     *
     * @return IJsonRpcCreate
     */
    public function setItemData(array $data): IJsonRpcCreate;
}
