<?php
namespace extas\components\jsonrpc;

use extas\components\Item;
use extas\components\SystemContainer;
use extas\interfaces\IItem;
use extas\interfaces\jsonrpc\IJsonRpcIndex;
use extas\interfaces\repositories\IRepository;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcIndex
 *
 * @package extas\components\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcIndex extends Item implements IJsonRpcIndex
{
    /**
     * @param ResponseInterface $response
     */
    public function dumpTo(ResponseInterface &$response)
    {
        /**
         * @var $repo IRepository
         * @var $records IItem[]
         */
        $repo = SystemContainer::getItem($this->getRepoName());
        $records = $repo->all([]);
        $items = [];
        $limit = $this->getLimit();

        foreach ($records as $record) {
            if ($limit && (count($items) < $limit)) {
                $items[] = $record->__toArray();
            }
        }

        $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200)
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => [
                    'items' => $items,
                    'total' => count($items)
                ]
            ]));
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return (int) ($this->config[static::FIELD__LIMIT] ?? 0);
    }

    /**
     * @return string
     */
    public function getRepoName(): string
    {
        return $this->config[static::FIELD__REPO_NAME] ?? '';
    }

    /**
     * @param int $limit
     *
     * @return IJsonRpcIndex
     */
    public function setLimit(int $limit): IJsonRpcIndex
    {
        $this->config[static::FIELD__LIMIT] = $limit;

        return $this;
    }

    /**
     * @param string $repoName
     *
     * @return IJsonRpcIndex
     */
    public function setRepoName(string $repoName): IJsonRpcIndex
    {
        $this->config[static::FIELD__REPO_NAME] = $repoName;

        return $this;
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return static::SUBJECT;
    }
}
