<?php
namespace extas\components\plugins\workflows\jsonrpc\specs;

use extas\components\plugins\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SpecsOperationOnePlugin
 *
 * @stage run.specs.*
 * @package extas\components\plugins\workflows\jsonrpc\specs
 * @author jeyroik@gmail.com
 */
class SpecsOperationOnePlugin extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $fileName = getenv('EXTAS__WF__OPERATION_ALL') ?: APP__ROOT . '/src/configs/operations.json';
        $specs = is_file($fileName) ? json_decode(file_get_contents($fileName), true) : [];
        $method = $jRpcData['method'] ?? 'app.index';

        if (isset($specs[$method])) {
            $specs = $specs[$method];
        }

        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'jsonrpc' => '2.0',
                'result' => $specs
            ]));
    }
}
