<?php
namespace extas\components\plugins\workflows\jsonrpc\schemas;

use extas\components\jsonrpc\JsonRpcIndex;
use extas\components\plugins\Plugin;
use extas\components\protocols\ProtocolExpand;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\parameters\IParameter;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcSchemaIndex
 *
 * @stage run.jsonrpc.schema.index
 * @package extas\components\plugins\workflows\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class JsonRpcSchemaIndex extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $protocol = new ProtocolExpand();
        $protocol($jRpcData, $request);

        $index = new JsonRpcIndex([
            JsonRpcIndex::FIELD__REPO_NAME => IWorkflowSchemaRepository::class,
            JsonRpcIndex::FIELD__LIMIT => $jRpcData['limit'] ?? 0,
            JsonRpcIndex::FIELD__ITEM_NAME => 'schema',
            JsonRpcIndex::FIELD__SERVER_REQUEST => $this->getServerRequest($jRpcData),
            JsonRpcIndex::FIELD__SERVER_RESPONSE => $this->getServerResponse($response)
        ]);
        $index->dumpTo($response, $jRpcData);
    }

    /**
     * @param $jRpcData
     *
     * @return IServerRequest
     */
    protected function getServerRequest($jRpcData): IServerRequest
    {
        return new ServerRequest([
            ServerRequest::FIELD__NAME => 'schema.index',
            ServerRequest::FIELD__PARAMETERS => ServerRequest::makeParametersFrom($jRpcData)
        ]);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return IServerResponse
     */
    protected function getServerResponse(ResponseInterface $response): IServerResponse
    {
        return new ServerResponse([
            ServerResponse::FIELD__NAME => 'schema.index',
            ServerResponse::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => ServerResponse::PARAMETER__HTTP_RESPONSE,
                    IParameter::FIELD__VALUE => $response,
                    IParameter::FIELD__TEMPLATE => ResponseInterface::class
                ]
            ]
        ]);
    }
}
