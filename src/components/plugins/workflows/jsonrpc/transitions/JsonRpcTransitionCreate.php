<?php
namespace extas\components\plugins\workflows\jsonrpc\transitions;

use extas\components\jsonrpc\JsonRpcCreate;
use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionCreate
 *
 * @stage run.jsonrpc.transition.create
 * @package extas\components\plugins\workflows\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionCreate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $create = new JsonRpcCreate([
            JsonRpcCreate::FIELD__ENTITY_NAME => 'transition',
            JsonRpcCreate::FIELD__REPO_NAME => IWorkflowTransitionRepository::class,
            JsonRpcCreate::FIELD__ITEM_CLASS => WorkflowTransition::class,
            JsonRpcCreate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $create->dumpTo($response, $jRpcData);
    }
}
