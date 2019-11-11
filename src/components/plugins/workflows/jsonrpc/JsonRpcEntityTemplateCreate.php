<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcCreate;
use extas\components\plugins\Plugin;
use extas\components\workflows\entities\WorkflowEntityTemplate;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcEntityTemplateCreate
 *
 * @stage run.jsonrpc.entity.template.create
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcEntityTemplateCreate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $jRpcData = [])
    {
        $create = new JsonRpcCreate([
            JsonRpcCreate::FIELD__REPO_NAME => IWorkflowEntityTemplateRepository::class,
            JsonRpcCreate::FIELD__ITEM_CLASS => WorkflowEntityTemplate::class,
            JsonRpcCreate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $create->dumpTo($response);
    }
}
