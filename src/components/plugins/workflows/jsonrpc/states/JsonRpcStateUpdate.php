<?php
namespace extas\components\plugins\workflows\jsonrpc\states;

use extas\components\jsonrpc\JsonRpcUpdate;
use extas\components\plugins\Plugin;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcStateUpdate
 *
 * @stage run.jsonrpc.state.update
 * @package extas\components\plugins\workflows\jsonrpc\states
 * @author jeyroik@gmail.com
 */
class JsonRpcStateUpdate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $update = new JsonRpcUpdate([
            JsonRpcUpdate::FIELD__REPO_NAME => IWorkflowStateRepository::class,
            JsonRpcUpdate::FIELD__ITEM_CLASS => WorkflowState::class,
            JsonRpcUpdate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $update->dumpTo($response, $jRpcData);
    }
}