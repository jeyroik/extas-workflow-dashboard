<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcCreate;
use extas\components\plugins\Plugin;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcSchemaCreate
 *
 * @stage run.jsonrpc.schema.create
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcSchemaCreate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $jRpcData = [])
    {
        $create = new JsonRpcCreate([
            JsonRpcCreate::FIELD__REPO_NAME => IWorkflowSchemaRepository::class,
            JsonRpcCreate::FIELD__ITEM_CLASS => WorkflowSchema::class,
            JsonRpcCreate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $create->dumpTo($response, $jRpcData);
    }
}
