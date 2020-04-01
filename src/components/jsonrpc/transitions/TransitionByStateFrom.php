<?php
namespace extas\components\jsonrpc\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\entities\WorkflowEntityContext;
use extas\components\workflows\transitions\results\TransitionResult;
use extas\components\workflows\Workflow;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\entities\IWorkflowEntity;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

/**
 * Class TransitionByStateFrom
 *
 * @stage run.jsonrpc.transition.by_state_from.index
 * @package extas\components\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class TransitionByStateFrom extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $jRpcData = $request->getParams();
        $schema = $this->getSchema($jRpcData);

        if (!$schema) {
            $response->error('Unknown schema', 400);
        } else {
            $entityTemplate = $schema->getEntityTemplate();
            if (!$entityTemplate) {
                $response->error('Missed entity template', 400);
            } else {
                $entity = $entityTemplate->buildClassWithParameters($jRpcData['entity'] ?? []);
                $transitions = $this->getTransitions($jRpcData, $schema);

                $result = [];
                $context = new WorkflowEntityContext($jRpcData['context'] ?? []);
                $filter = $request->getFilter();
                $filterNames = isset($filter['transition_name'], $filter['transition_name']['$in'])
                    ? array_flip($filter['transition_name']['$in'])
                    : [];

                foreach ($transitions as $transition) {
                    if ($this->isValid($transition, $entity, $schema, $context)) {
                        if (!empty($filterNames) && !isset($filterNames[$transition->getName()])) {
                            continue;
                        }
                        $result[] = $transition->__toArray();
                    }
                }

                $response->success($result);
            }
        }
    }

    /**
     * @param IWorkflowTransition $transition
     * @param IWorkflowEntity $entity
     * @param IWorkflowSchema $schema
     * @param $context
     * @return bool
     */
    protected function isValid($transition, $entity, $schema, $context): bool
    {
        $workflow = new Workflow();
        $transitionResult = new TransitionResult();
        $context[Workflow::CONTEXT__CONDITIONS] = true;
        $transitionResult = $workflow->isTransitionValid(
            $transition,
            $entity,
            $schema,
            $context,
            $transitionResult
        );

        return $transitionResult->isSuccess();
    }

    /**
     * @param array $jRpcData
     *
     * @return IWorkflowSchema|null
     */
    protected function getSchema($jRpcData): ?IWorkflowSchema
    {
        /**
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schema IWorkflowSchema
         */
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        return $schemaRepo->one([IWorkflowSchema::FIELD__NAME => $jRpcData['schema_name'] ?? '']);
    }

    /**
     * @param array $jRpcData
     * @param IWorkflowSchema $schema
     *
     * @return array|IWorkflowTransition[]
     */
    protected function getTransitions(array $jRpcData, IWorkflowSchema $schema): array
    {
        /**
         * @var $repo IWorkflowTransitionRepository
         * @var $transitions IWorkflowTransition[]
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $stateName = $jRpcData['state_name'] ?? '';

        return $repo->all([
            IWorkflowTransition::FIELD__NAME => $schema->getTransitionsNames(),
            IWorkflowTransition::FIELD__STATE_FROM => $stateName
        ]);
    }
}
