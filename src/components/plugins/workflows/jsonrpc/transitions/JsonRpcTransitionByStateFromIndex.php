<?php
namespace extas\components\plugins\workflows\jsonrpc\transitions;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\entities\WorkflowEntityContext;
use extas\components\workflows\transitions\results\TransitionResult;
use extas\components\workflows\Workflow;
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
        /**
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schema IWorkflowSchema
         * @var $repo IWorkflowTransitionRepository
         * @var $transitions IWorkflowTransition[]
         */
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schema = $schemaRepo->one([IWorkflowSchema::FIELD__NAME => $jRpcData['schema_name'] ?? '']);
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);

        if (!$schema) {
            $response->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'error' => [
                    'code' => JsonRpcErrors::ERROR__UNKNOWN_SCHEMA,
                    'data' => [IWorkflowSchema::FIELD__NAME => $jRpcData['schema_name'] ?? ''],
                    'message' => 'Unknown schema'
                ]
            ]));
        } else {
            $entity = $schema->getEntityTemplate()->buildClassWithParameters($jRpcData['entity'] ?? []);
            $workflow = new Workflow();
            $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
            $stateName = $jRpcData['state_name'] ?? '';
            $transitions = $repo->all([
                IWorkflowTransition::FIELD__NAME => $schema->getTransitionsNames(),
                IWorkflowTransition::FIELD__STATE_FROM => $stateName
            ]);

            $result = [];
            $context = new WorkflowEntityContext($jRpcData['context'] ?? []);
            $filter = $jRpcData['filter'] ?? [];
            $filterNames = isset($filter['transition_name'], $filter['transition_name']['$in'])
                ? array_flip($filter['transition_name']['$in'])
                : [];

            foreach ($transitions as $transition) {
                $transitionResult = new TransitionResult();
                if ($workflow->isTransitionValid($transition, $entity, $schema, $context, $transitionResult)) {
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
}
