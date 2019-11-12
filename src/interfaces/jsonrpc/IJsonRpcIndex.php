<?php
namespace extas\interfaces\jsonrpc;

use extas\interfaces\IItem;
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

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @return string
     */
    public function getRepoName(): string;

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
     * @param ResponseInterface $response
     * @param array $jRpcData
     *
     * @return void
     */
    public function dumpTo(ResponseInterface &$response, array $jRpcData);
}
