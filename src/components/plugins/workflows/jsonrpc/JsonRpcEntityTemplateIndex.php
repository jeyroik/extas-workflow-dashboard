<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcIndex;
use extas\components\plugins\Plugin;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcEntityTemplateIndex
 *
 * @stage run.jsonrpc.entity.template.index
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcEntityTemplateIndex extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $index = new JsonRpcIndex([
            JsonRpcIndex::FIELD__REPO_NAME => IWorkflowEntityTemplateRepository::class,
            JsonRpcIndex::FIELD__LIMIT => $jRpcData['limit'] ?? 0
        ]);
        $index->dumpTo($response, $jRpcData);
    }
}
