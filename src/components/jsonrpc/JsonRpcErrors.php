<?php
namespace extas\components\jsonrpc;

/**
 * Class JsonRpcErrors
 *
 * @package extas\components\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcErrors
{
    const ERROR__MARKER = '@error';

    const ERROR__ALREADY_EXIST = 1010;
    const ERROR__THERE_ARE_TRANSITIONS_TO_STATE = 10201;
    const ERROR__THERE_ARE_TRANSITIONS_FROM_STATE = 10202;
    const ERROR__UNKNOWN_ENTITY = 10404;
    const ERROR__THERE_ARE_SCHEMAS_WITH_TRANSITION = 10301;
    const ERROR__THERE_ARE_DISPATCHERS_FOR_TRANSITION = 10302;
    const ERROR__THERE_ARE_DISPATCHERS_BY_TEMPLATE = 10501;
    const ERROR__THE_SAME_STATE = 10603;
    const ERROR__UNKNOWN_STATES = 10601;
    const ERROR__UNKNOWN_TRANSITION = 10602;
    const ERROR__UNKNOWN_SCHEMA = 10701;
    const ERROR__UNKNOWN_TEMPLATE = 10702;

    /**
     * @var array
     */
    protected static $errorMap = [
        1010 => 'Already exist',
        10201 => 'There are transitions to this state. Please, delete them first',
        10202 => 'There are transitions from this state. Please, delete them first',
        10301 => 'There are schemas with this transition. Please, delete transition from them first',
        10302 => 'There are dispatchers for this transition. Please, delete them first',
        10404 => 'Unknown',
        10501 => 'There are dispatcher by this template. Please, delete them first',
        10601 => 'There are unknown states',
        10602 => 'There is unknown transition',
        10603 => 'The `from` and `to` states can not be the same',
        10701 => 'There is unknown schema',
        10702 => 'There is unknown template'
    ];

    /**
     * @param int $errorCode
     * @return mixed|string
     */
    public static function errorText(int $errorCode)
    {
        return static::$errorMap[$errorCode] ?? '';
    }
}
