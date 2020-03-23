<?php
namespace extas\components\jsonrpc\entities;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\entities\WorkflowEntityContext;
use extas\components\workflows\Workflow;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\entities\IWorkflowEntity;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

/**
 * Class EntityTransit
 *
 * @stage run.jsonrpc.entity.run
 * @package extas\components\jsonrpc\states
 * @author jeyroik@gmail.com
 */
class EntityTransit extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $jRpcData = $request->getParams();
        $entityData = $jRpcData['entity'] ?? [];
        $context = $jRpcData['context'] ?? [];
        $schemaName = $jRpcData['schema_name'] ?? '';
        $transitionName = $jRpcData['transition_name'] ?? '';

        /**
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schema IWorkflowSchema
         * @var $transitionRepo IWorkflowTransitionRepository
         * @var $transition IWorkflowTransition
         * @var $entity IWorkflowEntity
         */
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schema = $schemaRepo->one([IWorkflowSchema::FIELD__NAME => $schemaName]);

        if (!$schema) {
            $response->error('Unknown schema', 400);
        } else {
            $entityTemplate = $schema->getEntityTemplate();
            $entity = $entityTemplate->buildClassWithParameters($entityData);
            $entity->setTemplateName($entityTemplate->getName());

            $transitionRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
            $transition = $transitionRepo->one([IWorkflowTransition::FIELD__NAME => $transitionName]);

            if (!$transition) {
                $response->error('Unknown transition', 400);
            } else {
                $this->transit($entity, $context, $transition, $schema, $response);
            }
        }
    }

    /**
     * @param IWorkflowEntity $entity
     * @param array $context
     * @param IWorkflowTransition $transition
     * @param IWorkflowSchema $schema
     * @param IResponse $response
     */
    protected function transit(
        IWorkflowEntity $entity,
        array $context,
        IWorkflowTransition $transition,
        IWorkflowSchema $schema,
        IResponse &$response
    )
    {
        if ($transition->getStateFromName() != $entity->getStateName()) {
            $response->error('Can not transit entity to a transition', 400);
        } else {
            $result = Workflow::transitByTransition(
                $entity,
                $transition->getName(),
                $schema,
                new WorkflowEntityContext($context)
            );
            if (!$result->isSuccess()) {
                $response->error($result->getError()->getMessage(), 400);
            } else {
                $response->success($entity->__toArray());
            }
        }
    }
}
