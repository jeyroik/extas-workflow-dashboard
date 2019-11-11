<?php
namespace extas\components\jsonrpc;

use extas\components\SystemContainer;
use extas\interfaces\IItem;
use extas\interfaces\jsonrpc\IJsonRpcCreate;
use extas\interfaces\repositories\IRepository;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcUpdate
 *
 * @package extas\components\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcUpdate extends JsonRpcCreate implements IJsonRpcCreate
{
    /**
     * @param ResponseInterface $response
     */
    public function dumpTo(ResponseInterface &$response)
    {
        /**
         * @var $repo IRepository
         * @var $item IItem
         */
        $repo = SystemContainer::getItem($this->getRepoName());
        $itemClass = $this->getItemClass();
        $item = new $itemClass($this->getItemData());
        $repo->update($item);

        $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200)
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => $item->__toArray()
            ]));
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.jsonrpc.update';
    }
}
