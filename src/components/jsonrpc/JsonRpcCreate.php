<?php
namespace extas\components\jsonrpc;

use extas\components\SystemContainer;
use extas\interfaces\IHasName;
use extas\interfaces\IItem;
use extas\interfaces\jsonrpc\IJsonRpcCreate;
use extas\interfaces\repositories\IRepository;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcCreate
 *
 * @package extas\components\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcCreate extends JsonRpcIndex implements IJsonRpcCreate
{
    /**
     * @param ResponseInterface $response
     */
    public function dumpTo(ResponseInterface &$response)
    {
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);

        /**
         * @var $repo IRepository
         * @var $item IItem|IHasName
         */
        $repo = SystemContainer::getItem($this->getRepoName());
        $itemClass = $this->getItemClass();
        $item = new $itemClass($this->getItemData());
        $exist = $repo->one([IHasName::FIELD__NAME => $item->getName()]);
        if ($exist || !$item->getName()) {
            $response->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'error' => [
                    'code' => 1010,
                    'data' => [],
                    'message' => 'Already exist'
                ]
            ]));
        } else {
            $repo->create($item);
            $response
                ->getBody()->write(json_encode([
                    'id' => $jRpcData['id'] ?? '',
                    'result' => $item->__toArray()
                ]));
        }
    }

    /**
     * @return string
     */
    public function getItemClass(): string
    {
        return $this->config[static::FIELD__ITEM_CLASS] ?? '';
    }

    /**
     * @return array
     */
    public function getItemData(): array
    {
        return (array) ($this->config[static::FIELD__ITEM_DATA] ?? []);
    }

    /**
     * @param string $className
     *
     * @return IJsonRpcCreate
     */
    public function setItemClass(string $className): IJsonRpcCreate
    {
        $this->config[static::FIELD__ITEM_CLASS] = $className;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return IJsonRpcCreate
     */
    public function setItemData(array $data): IJsonRpcCreate
    {
        $this->config[static::FIELD__ITEM_DATA] = $data;

        return $this;
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.jsonrpc.create';
    }
}
