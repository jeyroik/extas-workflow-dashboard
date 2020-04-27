<?php
namespace extas\interfaces\extensions\jsonrpc;

use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Interface IDataExtensionSchemaTransitionCreate
 *
 * @package extas\interfaces\extensions\jsonrpc
 * @author jeyroik@gmail.com
 */
interface IDataExtensionSchemaTransitionCreate extends IDataExtension
{
    const FIELD__TRANSITION = 'transition';
    const FIELD__DISPATCHER = 'dispatcher';

    /**
     * @return ITransition|null
     */
    public function getTransition(): ?ITransition;

    /**
     * @return ITransitionDispatcher|null
     */
    public function getTransitionDispatcher(): ?ITransitionDispatcher;
}
