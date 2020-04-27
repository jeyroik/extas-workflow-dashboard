<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class WorkflowTransit
 *
 * @stage run.jsonrpc.entity.run
 * @package extas\components\jsonrpc\workflows
 * @author jeyroik@gmail.com
 */
class WorkflowTransit extends OperationDispatcher
{
    use TTransit;
    use TGetTransition;

    public const FIELD__ENTITY = 'entity';
    public const FIELD__CONTEXT = 'context';
    public const FIELD__TRANSITION_NAME = 'transition_name';

    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        list($entityData, $contextData, $transitionName) = $this->listData($request->getParams());

        try {
            $transition = $this->getTransition([ITransition::FIELD__NAME => $transitionName], $transitionName);
            $this->transit($contextData, $entityData, $transition, $response);
        } catch (\Exception $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    /**
     * @param array $jRpcData
     * @return array
     */
    protected function listData(array $jRpcData)
    {
        return [
            $jRpcData[static::FIELD__ENTITY] ?? [],
            $jRpcData[static::FIELD__CONTEXT] ?? [],
            $jRpcData[static::FIELD__TRANSITION_NAME] ?? ''
        ];
    }
}
