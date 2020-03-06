<?php
namespace extas\components\plugins\workflows\jsonrpc\before\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;

/**
 * Class BeforeSchemaDelete
 *
 * @stage before.run.jsonrpc.schema.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeSchemaDelete extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new WorkflowSchema($request->getData());
            $this->checkTransitionDispatchers($item);
        }
    }

    /**
     * @param IWorkflowSchema $item
     */
    protected function checkTransitionDispatchers(IWorkflowSchema $item)
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__SCHEMA_NAME => $item->getName()
        ]);
        if (count($dispatchers)) {
            $repo->delete([ITransitionDispatcher::FIELD__SCHEMA_NAME => $item->getName()]);
        }
    }
}
