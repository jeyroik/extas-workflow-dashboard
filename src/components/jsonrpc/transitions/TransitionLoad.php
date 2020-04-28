<?php
namespace extas\components\jsonrpc\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\jsonrpc\TLoad;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\transitions\ITransitionRepository;

/**
 * Class TransitionLoad
 *
 * @stage run.jsonrpc.transition.load
 * @package extas\components\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class TransitionLoad extends OperationDispatcher
{
    use TLoad;

    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $transitions = $request->getData();
        $this->defaultLoad(
            $transitions,
            SystemContainer::getItem(ITransitionRepository::class),
            Transition::class,
            $response
        );
    }
}
