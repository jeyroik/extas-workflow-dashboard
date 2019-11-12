<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\Plugin;
use extas\interfaces\IHasName;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcValidationPlugin
 *
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcValidationPlugin extends Plugin
{
    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param int $eCode
     * @param array $eData
     */
    protected function setResponseError(ResponseInterface &$response, array &$jRpcData, int $eCode, array $eData = [])
    {
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'error' => [
                    'code' => $eCode,
                    'data' => $eData,
                    'message' => JsonRpcErrors::errorText($eCode)
                ]
            ]));
        $this->markJRpcDataWithError($jRpcData);
    }

    /**
     * @param array $jRpcData
     */
    protected function markJRpcDataWithError(array &$jRpcData)
    {
        $jRpcData[JsonRpcErrors::ERROR__MARKER] = true;
    }

    /**
     * @param array $jRpcData
     *
     * @return bool
     */
    protected function isThereError(array $jRpcData)
    {
        return isset($jRpcData[JsonRpcErrors::ERROR__MARKER]);
    }

    /**
     * @param IHasName[] $items
     *
     * @return array
     */
    protected function prepare(array $items)
    {
        $prepared = [];
        foreach ($items as $item) {
            $prepared[] = [
                IHasName::FIELD__NAME => $item->getName()
            ];
        }

        return $prepared;
    }
}
