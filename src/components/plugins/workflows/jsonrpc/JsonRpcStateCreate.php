<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcCreate;
use extas\components\plugins\Plugin;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcStateCreate
 *
 * @stage run.jsonrpc.state.create
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcStateCreate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $create = new JsonRpcCreate([
            JsonRpcCreate::FIELD__REPO_NAME => IWorkflowStateRepository::class,
            JsonRpcCreate::FIELD__ITEM_CLASS => WorkflowState::class,
            JsonRpcCreate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $create->dumpTo($response, $jRpcData);
    }
}
