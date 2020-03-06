<?php
namespace extas\components\jsonrpc\states;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;

/**
 * Class StateLoad
 *
 * @stage run.jsonrpc.state.load
 * @package extas\components\jsonrpc\states
 * @author jeyroik@gmail.com
 */
class StateLoad extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $states = $request->getData();
        $statesNames = array_column($states, IWorkflowState::FIELD__NAME);
        $statesByName = array_column($states, null, IWorkflowState::FIELD__NAME);

        /**
         * @var $stateRepo IWorkflowStateRepository
         * @var $existed IWorkflowState[]
         */
        $stateRepo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $existed = $stateRepo->all([IWorkflowState::FIELD__NAME => $statesNames]);

        foreach ($existed as $existingState) {
            if (isset($statesByName[$existingState->getName()])) {
                unset($statesByName[$existingState->getName()]);
            }
        }

        $created = 0;

        foreach ($statesByName as $stateData) {
            $state = new WorkflowState($stateData);
            $stateRepo->create($state);
            $created++;
        }

        $response->success([
            'created_count' => $created,
            'got_count' => count($states)
        ]);
    }
}
