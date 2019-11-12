<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcCreate;
use extas\components\plugins\Plugin;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionDispatcherTemplateCreate
 *
 * @stage run.jsonrpc.transition.dispatcher.template.create
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionDispatcherTemplateCreate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $create = new JsonRpcCreate([
            JsonRpcCreate::FIELD__REPO_NAME => ITransitionDispatcherTemplateRepository::class,
            JsonRpcCreate::FIELD__ITEM_CLASS => TransitionDispatcherTemplate::class,
            JsonRpcCreate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $create->dumpTo($response, $jRpcData);
    }
}
