<?php
namespace extas\components\plugins\workflows\jsonrpc\transitions;

use extas\components\jsonrpc\JsonRpcDelete;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionDelete
 *
 * @stage run.jsonrpc.transition.delete
 * @package extas\components\plugins\workflows\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionDelete extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $operation = new JsonRpcDelete([
            JsonRpcDelete::FIELD__REPO_NAME => IWorkflowTransitionRepository::class,
            JsonRpcDelete::FIELD__ITEM_CLASS => WorkflowTransition::class,
            JsonRpcDelete::FIELD__ITEM_DATA => $jRpcData
        ]);

        $operation->dumpTo($response, $jRpcData);
    }
}
