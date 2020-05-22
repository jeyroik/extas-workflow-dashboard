<?php
namespace extas\components\jsonrpc;

use extas\interfaces\IHasName;
use extas\interfaces\jsonrpc\IRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait TLoad
 *
 * @method successResponse(string $id, array $data = [])
 *
 * @package extas\components\jsonrpc
 * @author jeyroik@gmail.com
 */
trait TLoad
{
    /**
     * @param $items
     * @param $repo
     * @param $itemClass
     * @param IRequest $request
     * @return ResponseInterface
     */
    protected function defaultLoad($items, $repo, $itemClass, IRequest $request): ResponseInterface
    {
        $names = array_column($items, IHasName::FIELD__NAME);
        $byName = array_column($items, null, IHasName::FIELD__NAME);

        $existed = $repo->all([IHasName::FIELD__NAME => $names]);
        $existedNames = [];
        foreach ($existed as $item) {
            $existedNames[$item->getName()] = true;
        }

        $forCreating = array_intersect_key($byName, $existedNames);
        $created = 0;

        foreach ($forCreating as $data) {
            $item = new $itemClass($data);
            $repo->create($item);
            $created++;
        }

        return $this->successResponse($request->getId(), [
            'created_count' => $created,
            'got_count' => count($items)
        ]);
    }
}
