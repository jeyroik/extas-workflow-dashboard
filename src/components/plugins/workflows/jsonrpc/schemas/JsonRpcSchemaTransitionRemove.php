<?php
namespace extas\components\plugins\workflows\jsonrpc\schemas;

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
 * Class JsonRpcSchemaTransitionRemove
 *
 * @stage run.jsonrpc.schema.transition.remove
 * @package extas\components\plugins\workflows\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class JsonRpcSchemaTransitionRemove extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
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
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);

        if (!$schema) {
            $response->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'error' => [
                    'code' => JsonRpcErrors::ERROR__UNKNOWN_SCHEMA,
                    'data' => [IWorkflowSchema::FIELD__NAME => $schemaName],
                    'message' => 'Unknown schema'
                ]
            ]));
        } else {
            if ($schema->hasTransition($transitionName)) {
                $schema->removeTransition($transition);
                $schemaRepo->update($schema);
            }

            $response->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => [
                    'name' => $transitionName
                ]
            ]));
        }
    }
}
