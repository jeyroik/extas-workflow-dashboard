<?php
namespace extas\components\jsonrpc;

use extas\components\Item;
use extas\interfaces\jsonrpc\IJsonRpcData;

/**
 * Class JsonRpcData
 *
 * Класс обёртка для jsonrpc данных.
 * Рекомендуется создание расширений для данного класса для доступа к полям данных.
 *
 * @package extas\components\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcData extends Item implements IJsonRpcData
{
    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return static::SUBJECT;
    }
}
