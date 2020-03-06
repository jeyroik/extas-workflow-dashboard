<?php
namespace extas\components\plugins\workflows\jsonrpc\before\states;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\IHasName;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

/**
 * Class BeforeStateDelete
 *
 * @stage before.run.jsonrpc.state.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeStateDelete extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new WorkflowState($request->getData());
            /**
             * @var $repo IRepository
             */
            $repo = SystemContainer::getItem(IWorkflowStateRepository::class);
            if (!$repo->one([IHasName::FIELD__NAME => $item->getName()])) {
                $response->error('Unknown state', 400);
            } else {
                $this->checkTransitionsTo($response, $item);
                $this->checkTransitionsFrom($response, $item);
            }
        }
    }

    /**
     * @param IResponse $response
     * @param IWorkflowState $item
     */
    protected function checkTransitionsTo(IResponse &$response, IWorkflowState $item)
    {
        /**
         * @var $transitRepo IWorkflowTransitionRepository
         * @var $transitionsToState IWorkflowTransition[]
         */
        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transitionsToState = $transitRepo->all([
            IWorkflowTransition::FIELD__STATE_TO => $item->getName()
        ]);
        if (count($transitionsToState)) {
            $response->error('There are transitions to a state', 400);
        }
    }

    /**
     * @param IResponse $response
     * @param IWorkflowState $item
     */
    protected function checkTransitionsFrom(IResponse &$response, IWorkflowState $item)
    {
        /**
         * @var $transitRepo IWorkflowTransitionRepository
         */
        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transitionsFromState = $transitRepo->all([
            IWorkflowTransition::FIELD__STATE_FROM => $item->getName()
        ]);
        if (count($transitionsFromState)) {
            $response->error('There are transitions from a state', 400);
        }
    }
}
