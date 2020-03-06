<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;

/**
 * Class BeforeTransitionDelete
 *
 * @stage before.run.jsonrpc.transition.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDelete extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new WorkflowTransition($request->getData());
            $this->checkSchemas($response, $item);
            $this->checkTransitionDispatchers($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param IWorkflowTransition $item
     */
    protected function checkSchemas(IResponse &$response, IWorkflowTransition $item)
    {
        /**
         * @var $repo IWorkflowSchemaRepository
         * @var $schemas IWorkflowSchema[]
         */
        $repo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schemas = $repo->all([
            IWorkflowSchema::FIELD__TRANSITIONS => $item->getName()
        ]);
        if (count($schemas)) {
            $response->error('There are schemas with a transition', 400);
        }
    }

    /**
     * @param IResponse $response
     * @param IWorkflowTransition $item
     */
    protected function checkTransitionDispatchers(IResponse &$response, IWorkflowTransition $item)
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__TRANSITION_NAME => $item->getName()
        ]);
        if (count($dispatchers)) {
            $response->error('There are dispatchers for a transition', 400);
        }
    }
}
