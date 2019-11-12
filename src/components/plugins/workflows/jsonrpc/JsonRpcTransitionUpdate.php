<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcUpdate;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionUpdate
 *
 * @stage run.jsonrpc.transition.update
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionUpdate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $jRpcData = [])
    {
        $update = new JsonRpcUpdate([
            JsonRpcUpdate::FIELD__REPO_NAME => IWorkflowTransitionRepository::class,
            JsonRpcUpdate::FIELD__ITEM_CLASS => WorkflowTransition::class,
            JsonRpcUpdate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $update->dumpTo($response, $jRpcData);
    }
}
