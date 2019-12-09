<?php
namespace extas\interfaces\jsonrpc;

use extas\interfaces\IItem;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface IJsonRpcIndex
 *
 * @package extas\interfaces\jsonrpc
 * @author jeyroik@gmail.com
 */
interface IJsonRpcIndex extends IItem
{
    const SUBJECT = 'extas.jsonrpc.index';

    const FIELD__LIMIT = 'limit';
    const FIELD__REPO_NAME = 'repo';
    const FIELD__ITEM_NAME = 'item';
    const FIELD__SERVER_REQUEST = 'server_request';
    const FIELD__SERVER_RESPONSE = 'server_response';

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @return string
     */
    public function getRepoName(): string;

    /**
     * @return string
     */
    public function getItemName(): string;

    /**
     * @return IServerRequest|null
     */
    public function getServerRequest(): ?IServerRequest;

    /**
     * @return IServerResponse|null
     */
    public function getServerResponse(): ?IServerResponse;

    /**
     * @param int $limit
     *
     * @return IJsonRpcIndex
     */
    public function setLimit(int $limit): IJsonRpcIndex;

    /**
     * @param string $repoName
     *
     * @return IJsonRpcIndex
     */
    public function setRepoName(string $repoName): IJsonRpcIndex;

    /**
     * @param string $name
     *
     * @return IJsonRpcIndex
     */
    public function setItemName(string $name): IJsonRpcIndex;

    /**
     * @param IServerResponse $response
     *
     * @return IJsonRpcIndex
     */
    public function setServerResponse(IServerResponse $response): IJsonRpcIndex;

    /**
     * @param IServerRequest $request
     *
     * @return IJsonRpcIndex
     */
    public function setServerRequest(IServerRequest $request): IJsonRpcIndex;

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     *
     * @return void
     */
    public function dumpTo(ResponseInterface &$response, array $jRpcData);
}
