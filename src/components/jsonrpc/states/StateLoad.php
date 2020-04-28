<?php
namespace extas\components\jsonrpc\states;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\jsonrpc\TLoad;
use extas\components\SystemContainer;
use extas\components\workflows\states\State;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
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
    use TLoad;

    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $states = $request->getData();
        $this->defaultLoad(
            $states,
            SystemContainer::getItem(IStateRepository::class),
            State::class,
            $response
        );
    }
}
