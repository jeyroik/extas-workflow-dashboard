<?php
namespace extas\components\plugins\workflows\jsonrpc\transitions;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\entities\WorkflowEntityContext;
use extas\components\workflows\transitions\results\TransitionResult;
use extas\components\workflows\Workflow;
use extas\interfaces\workflows\entities\IWorkflowEntity;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionByStateIndex
 *
 * @stage run.jsonrpc.transition.by_state_from.index
 * @package extas\components\plugins\workflows\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionByStateFromIndex extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $schema = $this->getSchema($jRpcData);
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);

        if (!$schema) {
            $response->getBody()->write($this->fail($jRpcData));
        } else {
            $entity = $schema->getEntityTemplate()->buildClassWithParameters($jRpcData['entity'] ?? []);
            $transitions = $this->getTransitions($jRpcData, $schema);

            $result = [];
            $context = new WorkflowEntityContext($jRpcData['context'] ?? []);
            $filter = $jRpcData['filter'] ?? [];
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

            $response->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => $result
            ]));
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
     * @param $jRpcData
     *
     * @return string
     */
    protected function fail($jRpcData)
    {
        return json_encode([
            'id' => $jRpcData['id'] ?? '',
            'error' => [
                'code' => JsonRpcErrors::ERROR__UNKNOWN_SCHEMA,
                'data' => [IWorkflowSchema::FIELD__NAME => $jRpcData['schema_name'] ?? ''],
                'message' => 'Unknown schema'
            ]
        ]);
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
