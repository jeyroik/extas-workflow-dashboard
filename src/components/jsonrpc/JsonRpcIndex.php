<?php
namespace extas\components\jsonrpc;

use extas\components\expands\Expander;
use extas\components\Item;
use extas\components\SystemContainer;
use extas\interfaces\IItem;
use extas\interfaces\jsonrpc\IJsonRpcIndex;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;
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
     * @param array $jRpcData
     */
    public function dumpTo(ResponseInterface &$response, array $jRpcData)
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
            if (!$limit || ($limit && (count($items) < $limit))) {
                $items[] = $record->__toArray();
            }
        }

        if ($this->getServerRequest()) {
            $box = Expander::getExpandingBox('index', $this->getItemName());
            $box->setData([$this->getItemName() . 's' => $items]);
            $box->expand($this->getServerRequest(), $this->getServerResponse());
            $box->pack();
            $items = $box->getValue()[$this->getItemName() . 's'];
        }

        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => [
                    'items' => $items,
                    'total' => count($items)
                ]
            ]));
    }

    /**
     * @return IServerRequest|null
     */
    public function getServerRequest(): ?IServerRequest
    {
        return $this->config[static::FIELD__SERVER_REQUEST] ?? null;
    }

    /**
     * @return IServerResponse|null
     */
    public function getServerResponse(): ?IServerResponse
    {
        return $this->config[static::FIELD__SERVER_RESPONSE] ?? null;
    }

    /**
     * @return string
     */
    public function getItemName(): string
    {
        return $this->config[static::FIELD__ITEM_NAME] ?? '';
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
     * @param IServerRequest $request
     *
     * @return IJsonRpcIndex
     */
    public function setServerRequest(IServerRequest $request): IJsonRpcIndex
    {
        $this->config[static::FIELD__SERVER_REQUEST] = $request;

        return $this;
    }

    /**
     * @param IServerResponse $response
     *
     * @return IJsonRpcIndex
     */
    public function setServerResponse(IServerResponse $response): IJsonRpcIndex
    {
        $this->config[static::FIELD__SERVER_RESPONSE] = $response;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return IJsonRpcIndex
     */
    public function setItemName(string $name): IJsonRpcIndex
    {
        $this->config[static::FIELD__ITEM_NAME] = $name;

        return $this;
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
