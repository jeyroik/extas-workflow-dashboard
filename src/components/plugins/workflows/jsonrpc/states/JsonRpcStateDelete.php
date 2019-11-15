<?php
namespace extas\components\plugins\workflows\jsonrpc\states;

use extas\components\jsonrpc\JsonRpcDelete;
use extas\components\plugins\Plugin;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcStateDelete
 *
 * @stage run.jsonrpc.state.delete
 * @package extas\components\plugins\workflows\jsonrpc\states
 * @author jeyroik@gmail.com
 */
class JsonRpcStateDelete extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $operation = new JsonRpcDelete([
            JsonRpcDelete::FIELD__REPO_NAME => IWorkflowStateRepository::class,
            JsonRpcDelete::FIELD__ITEM_CLASS => WorkflowState::class,
            JsonRpcDelete::FIELD__ITEM_DATA => $jRpcData
        ]);

        $operation->dumpTo($response, $jRpcData);
    }
}
