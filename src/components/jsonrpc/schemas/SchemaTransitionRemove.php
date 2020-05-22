<?php
namespace extas\components\jsonrpc\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\workflows\exceptions\transitions\ExceptionTransitionMissed;
use extas\interfaces\workflows\exceptions\transitions\IExceptionTransitionMissed;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SchemaTransitionRemove
 *
 * @deprecated use workflow.transition.delete
 *
 * @jsonrpc_operation
 * @jsonrpc_name workflow.schema.transition.remove
 * @jsonrpc_title Remove transition from a schema
 * @jsonrpc_description This is deprecated method! Use workflow.transition.delete.
 * @jsonrpc_request_field schema_name:string
 * @jsonrpc_request_field transition_name:string
 * @jsonrpc_response_field name:string
 *
 * @method workflowTransitionRepository()
 * @method workflowTransitionDispatcherRepository()
 *
 * @stage run.jsonrpc.schema.transition.remove
 * @package extas\components\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class SchemaTransitionRemove extends OperationDispatcher
{
    use TGetSchema;

    /**
     * @return ResponseInterface
     * @throws IExceptionTransitionMissed
     */
    public function __invoke(): ResponseInterface
    {
        $request = $this->convertPsrToJsonRpcRequest();
        $jRpcData = $request->getParams();
        $transitionName = $jRpcData['transition_name'] ?? '';
        $schemaName = $jRpcData['schema_name'] ?? '';

        try {
            $schema = $this->getSchema($schemaName);
            $this->checkTransition($transitionName);

            if ($schema->hasTransition($transitionName)) {
                $schema->removeTransition($transitionName);
                $this->updateSchema($schema);
                $this->removeTransitionAndDispatchers($transitionName);
            }

            return $this->successResponse($request->getId(), ['name' => $transitionName]);
        } catch (\Exception $e) {
            return $this->errorResponse($request->getId(), $e->getMessage(), 400);
        }
    }

    /**
     * @param string $transitionName
     */
    protected function removeTransitionAndDispatchers(string $transitionName): void
    {
        $this->workflowTransitionRepository()->delete([ITransition::FIELD__NAME => $transitionName]);
        $this->workflowTransitionDispatcherRepository()->delete([
            ITransitionDispatcher::FIELD__TRANSITION_NAME => $transitionName
        ]);
    }

    /**
     * @param string $transitionName
     * @return mixed
     * @throws ExceptionTransitionMissed
     */
    protected function checkTransition(string $transitionName)
    {
        $transition = $this->workflowTransitionRepository()->one([ITransition::FIELD__NAME => $transitionName]);

        if (!$transition) {
            throw new ExceptionTransitionMissed($transitionName);
        }

        return $transition;
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.workflow.schema.transition.remove';
    }
}
