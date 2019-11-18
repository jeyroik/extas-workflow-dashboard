<?php
namespace extas\components\plugins\workflows\jsonrpc\transitions;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
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
            $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
            $stateName = $jRpcData['state_name'] ?? '';
            $transitions = $repo->all([
                IWorkflowTransition::FIELD__NAME => $schema->getTransitionsNames(),
                IWorkflowTransition::FIELD__STATE_FROM => $stateName
            ]);

            $result = [];
            foreach ($transitions as $transition) {
                $result[] = $transition->__toArray();
            }

            $response->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => $result
            ]));
        }
    }
}
