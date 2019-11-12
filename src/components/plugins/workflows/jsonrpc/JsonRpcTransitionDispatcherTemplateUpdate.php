<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcUpdate;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionDispatcherTemplateUpdate
 *
 * @stage run.jsonrpc.transition.dispatcher.template.update
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionDispatcherTemplateUpdate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $update = new JsonRpcUpdate([
            JsonRpcUpdate::FIELD__REPO_NAME => ITransitionDispatcherTemplateRepository::class,
            JsonRpcUpdate::FIELD__ITEM_CLASS => TransitionDispatcherTemplate::class,
            JsonRpcUpdate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $update->dumpTo($response, $jRpcData);
    }
}
