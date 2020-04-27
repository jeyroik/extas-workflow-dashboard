<?php
namespace extas\components\jsonrpc\states;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\states\State;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\states\IStateRepository;

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
        $statesNames = array_column($states, IState::FIELD__NAME);
        $statesByName = array_column($states, null, IState::FIELD__NAME);

        /**
         * @var $stateRepo IStateRepository
         * @var $existed IState[]
         */
        $stateRepo = SystemContainer::getItem(IStateRepository::class);
        $existed = $stateRepo->all([IState::FIELD__NAME => $statesNames]);
        $existedNames = [];
        foreach ($existed as $item) {
            $existedNames[$item->getName()] = true;
        }

        $statesForCreate = array_intersect_key($statesByName, $existedNames);
        $created = 0;

        foreach ($statesForCreate as $stateData) {
            $state = new State($stateData);
            $stateRepo->create($state);
            $created++;
        }

        $response->success([
            'created_count' => $created,
            'got_count' => count($states)
        ]);
    }
}
