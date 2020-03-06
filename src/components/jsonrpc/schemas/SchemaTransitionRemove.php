<?php
namespace extas\components\jsonrpc\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

/**
 * Class SchemaTransitionRemove
 *
 * @stage run.jsonrpc.schema.transition.remove
 * @package extas\components\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class SchemaTransitionRemove extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $jRpcData = $request->getData();
        $transitionName = $jRpcData['transition_name'] ?? '';
        $schemaName = $jRpcData['schema_name'] ?? '';

        /**
         * @var $transitRepo IWorkflowTransitionRepository
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schema IWorkflowSchema
         */
        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transition = $transitRepo->one([IWorkflowTransition::FIELD__NAME => $transitionName]);
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schema = $schemaRepo->one([IWorkflowSchema::FIELD__NAME => $schemaName]);

        if (!$schema) {
            $response->error('Unknown schema', 400);
        } else {
            if ($schema->hasTransition($transitionName)) {
                $schema->removeTransition($transition);
                $schemaRepo->update($schema);
            }

            $response->success(['name' => $transitionName]);
        }
    }
}
