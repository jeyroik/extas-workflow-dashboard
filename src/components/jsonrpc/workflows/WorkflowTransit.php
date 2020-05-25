<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\ResponseInterface;

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
class WorkflowTransit extends OperationDispatcher
{
    use TTransit;
    use TGetTransition;

    public const FIELD__ENTITY = 'entity';
    public const FIELD__CONTEXT = 'context';
    public const FIELD__TRANSITION_NAME = 'transition_name';

    /**
     * @return ResponseInterface
     */
    public function __invoke(): ResponseInterface
    {
        $request = $this->convertPsrToJsonRpcRequest();
        list($entityData, $contextData, $transitionName) = $this->listData($request->getParams());

        try {
            $transition = $this->getTransition([ITransition::FIELD__NAME => $transitionName], $transitionName);
            return $this->transit($contextData, $entityData, $transition, $request);
        } catch (\Exception $e) {
            return $this->errorResponse($request->getId(), $e->getMessage(), 400);
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

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'workflow.transit';
    }
}
