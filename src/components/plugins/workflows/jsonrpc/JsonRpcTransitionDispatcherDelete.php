<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcDelete;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionDispatcherDelete
 *
 * @stage run.jsonrpc.transition.dispatcher.delete
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionDispatcherDelete extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $jRpcData = [])
    {
        $operation = new JsonRpcDelete([
            JsonRpcDelete::FIELD__REPO_NAME => ITransitionDispatcherRepository::class,
            JsonRpcDelete::FIELD__ITEM_CLASS => TransitionDispatcher::class,
            JsonRpcDelete::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $operation->dumpTo($response);
    }
}
