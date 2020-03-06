<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;

/**
 * Class BeforeTransitionCreate
 *
 * @stage before.run.jsonrpc.transition.create
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionCreate extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new WorkflowTransition($request->getData());
            $this->checkStates($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param IWorkflowTransition $item
     */
    protected function checkStates(IResponse &$response, IWorkflowTransition $item)
    {
        $states = [
            $item->getStateFromName(),
            $item->getStateToName()
        ];
        /**
         * @var $repo IWorkflowStateRepository
         * @var $wStates IWorkflowState[]
         */
        $repo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $wStates = $repo->all([IWorkflowState::FIELD__NAME => $states]);

        if ($item->getStateFromName() == $item->getStateToName()) {
            $response->error('The same state', 400);
        } elseif (count($wStates) != count($states)) {
            $states = array_flip($states);
            foreach ($wStates as $state) {
                unset($states[$state->getName()]);
            }
            $response->error('Unknown states', 400);
        }
    }
}
