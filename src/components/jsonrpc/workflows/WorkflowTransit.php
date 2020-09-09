<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\api\jsonrpc\operations\OperationRunner;
use extas\components\exceptions\MissedOrUnknown;
use extas\components\workflows\exceptions\transitions\ExceptionTransitionMissed;
use extas\interfaces\http\IHasHttpIO;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Class WorkflowTransit
 *
 * @jsonrpc_operation
 * @jsonrpc_name workflow.transit
 * @jsonrpc_title Workflow transit
 * @jsonrpc_description Transit entity through a workflow
 * @jsonrpc_request_field entity:object
 * @jsonrpc_request_field transition_name
 * @jsonrpc_request_field context:object
 * @jsonrpc_response_field entity:object
 *
 * @stage run.jsonrpc.entity.run
 * @package extas\components\jsonrpc\workflows
 * @author jeyroik@gmail.com
 */
class WorkflowTransit extends OperationRunner implements IHasHttpIO
{
    use TTransit;
    use TGetTransition;

    public const FIELD__ENTITY = 'entity';
    public const FIELD__CONTEXT = 'context';
    public const FIELD__TRANSITION_NAME = 'transition_name';

    /**
     * @return array
     * @throws MissedOrUnknown
     */
    public function run(): array
    {
        $request = $this->getJsonRpcRequest();
        list($entityData, $contextData, $transitionName) = $this->listData($request->getParams());

        $transition = $this->getTransition([ITransition::FIELD__NAME => $transitionName], $transitionName);

        return $this->transit($contextData, $entityData, $transition);
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

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'workflow.transit';
    }
}
