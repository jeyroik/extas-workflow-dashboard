<?php
namespace extas\components\plugins\workflows\jsonrpc\schemas;

use extas\components\jsonrpc\JsonRpcUpdate;
use extas\components\plugins\Plugin;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcSchemaUpdate
 *
 * @stage run.jsonrpc.schema.update
 * @package extas\components\plugins\workflows\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class JsonRpcSchemaUpdate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $update = new JsonRpcUpdate([
            JsonRpcUpdate::FIELD__REPO_NAME => IWorkflowSchemaRepository::class,
            JsonRpcUpdate::FIELD__ITEM_CLASS => WorkflowSchema::class,
            JsonRpcUpdate::FIELD__ITEM_DATA => $jRpcData['data'] ?? []
        ]);

        $update->dumpTo($response, $jRpcData);
    }
}