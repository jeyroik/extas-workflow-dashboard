<?php
namespace extas\components\plugins\workflows\jsonrpc\before\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\schemas\Schema;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;

/**
 * Class BeforeSchemaCreate
 *
 * @stage before.run.jsonrpc.schema.create
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeSchemaCreate extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new Schema($request->getData());
            /**
             * @var $repo ISchemaRepository
             */
            $repo = SystemContainer::getItem(ISchemaRepository::class);
            if ($repo->one([ISchema::FIELD__NAME => $item->getName()])) {
                $response->error('Schema already exist', 400);
            } else {
                /**
                 * 1. Создание схемы.
                 * 2. Создание перехода.
                 * 3. Добавление перехода в схему.
                 * Поэтому при создании схемы она не может содержать переходы.
                 */
                $item->setTransitionsNames([]);
            }
        }
    }
}
