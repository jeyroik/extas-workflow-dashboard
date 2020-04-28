<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\ITransition;

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
            $item = new Transition($request->getData());
            $this->deleteTransitionDispatchers($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransition $item
     */
    protected function deleteTransitionDispatchers(IResponse &$response, ITransition $item)
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $repo->delete([ITransitionDispatcher::FIELD__TRANSITION_NAME => $item->getName()]);
    }
}
