<?php
namespace extas\components\jsonrpc;

use extas\components\SystemContainer;
use extas\interfaces\IHasName;
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
     * @param array $jRpcData
     */
    public function dumpTo(ResponseInterface &$response, array $jRpcData)
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
        if (!$exist) {
            $response
                ->getBody()->write(json_encode([
                    'id' => $jRpcData['id'] ?? '',
                    'error' => [
                        'code' => 10404,
                        'data' => [],
                        'message' => 'Unknown'
                    ]
                ]));
        } else {
            $repo->update($item);
            $response
                ->getBody()->write(json_encode([
                    'id' => $jRpcData['id'] ?? '',
                    'result' => [$item->__toArray()]
                ]));
        }
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.jsonrpc.update';
    }
}
