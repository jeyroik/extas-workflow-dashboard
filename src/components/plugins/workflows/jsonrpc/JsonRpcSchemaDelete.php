<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcDelete;
use extas\components\plugins\Plugin;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcSchemaDelete
 *
 * @stage run.jsonrpc.schema.delete
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcSchemaDelete extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $jRpcData = [])
    {
        $operation = new JsonRpcDelete([
            JsonRpcDelete::FIELD__REPO_NAME => IWorkflowSchemaRepository::class,
            JsonRpcDelete::FIELD__ITEM_CLASS => WorkflowSchema::class,
            JsonRpcDelete::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $operation->dumpTo($response);
    }
}
