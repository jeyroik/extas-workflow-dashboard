<?php
namespace extas\components\plugins\workflows\jsonrpc\transitions\dispatchers;

use extas\components\jsonrpc\JsonRpcCreate;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionDispatcherCreate
 *
 * @stage run.jsonrpc.transition.dispatcher.create
 * @package extas\components\plugins\workflows\jsonrpc\transitions\dispatchers
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionDispatcherCreate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $create = new JsonRpcCreate([
            JsonRpcCreate::FIELD__REPO_NAME => ITransitionDispatcherRepository::class,
            JsonRpcCreate::FIELD__ITEM_CLASS => TransitionDispatcher::class,
            JsonRpcCreate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $create->dumpTo($response, $jRpcData);
    }
}
