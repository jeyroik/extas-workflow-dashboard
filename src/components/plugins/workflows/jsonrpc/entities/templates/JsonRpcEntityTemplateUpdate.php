<?php
namespace extas\components\plugins\workflows\jsonrpc\entities\templates;

use extas\components\jsonrpc\JsonRpcUpdate;
use extas\components\plugins\Plugin;
use extas\components\workflows\entities\WorkflowEntityTemplate;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcEntityTemplateUpdate
 *
 * @stage run.jsonrpc.entity.template.update
 * @package extas\components\plugins\workflows\jsonrpc\states\templates
 * @author jeyroik@gmail.com
 */
class JsonRpcEntityTemplateUpdate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $update = new JsonRpcUpdate([
            JsonRpcUpdate::FIELD__REPO_NAME => IWorkflowEntityTemplateRepository::class,
            JsonRpcUpdate::FIELD__ITEM_CLASS => WorkflowEntityTemplate::class,
            JsonRpcUpdate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $update->dumpTo($response, $jRpcData);
    }
}