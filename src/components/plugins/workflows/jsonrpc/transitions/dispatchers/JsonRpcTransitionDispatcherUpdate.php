<?php
namespace extas\components\plugins\workflows\jsonrpc\transitions\dispatchers;

use extas\components\jsonrpc\JsonRpcUpdate;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionDispatcherUpdate
 *
 * @stage run.jsonrpc.transition.dispatcher.update
 * @package extas\components\plugins\workflows\jsonrpc\transitions\dispatchers
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionDispatcherUpdate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $update = new JsonRpcUpdate([
            JsonRpcUpdate::FIELD__ENTITY_NAME => 'transition dispatcher',
            JsonRpcUpdate::FIELD__REPO_NAME => ITransitionDispatcherRepository::class,
            JsonRpcUpdate::FIELD__ITEM_CLASS => TransitionDispatcher::class,
            JsonRpcUpdate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $update->dumpTo($response, $jRpcData);
    }
}
