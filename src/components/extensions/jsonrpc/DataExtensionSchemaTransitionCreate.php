<?php
namespace extas\components\extensions\jsonrpc;

use extas\components\extensions\Extension;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\extensions\jsonrpc\IDataExtensionSchemaTransitionCreate;
use extas\interfaces\jsonrpc\IJsonRpcData;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\IWorkflowTransition;

/**
 * Class DataExtensionSchemaTransitionCreate
 *
 * @subject extas.jsonrpc.data
 * @package extas\components\extensions\jsonrpc
 * @author jeyroik@gmail.com
 */
class DataExtensionSchemaTransitionCreate extends Extension implements IDataExtensionSchemaTransitionCreate
{
    use THasData;

    /**
     * @param IJsonRpcData|null $data
     *
     * @return IWorkflowTransition|null
     */
    public function getTransition(IJsonRpcData $data = null): ?IWorkflowTransition
    {
        $createData = $this->getData($data->__toArray());
        if (isset($createData[static::FIELD__TRANSITION])) {
            return new WorkflowTransition($createData[static::FIELD__TRANSITION]);
        }

        return null;
    }

    /**
     * @param IJsonRpcData|null $data
     *
     * @return ITransitionDispatcher|null
     */
    public function getTransitionDispatcher(IJsonRpcData $data = null): ?ITransitionDispatcher
    {
        $createData = $this->getData($data->__toArray());
        if (isset($createData[static::FIELD__DISPATCHER])) {
            return new TransitionDispatcher($createData[static::FIELD__DISPATCHER]);
        }

        return null;
    }
}
