<?php
namespace extas\components\plugins\workflows\jsonrpc\before\states;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\states\State;
use extas\interfaces\IHasName;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\states\IStateRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

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
            $item = new State($request->getData());
            /**
             * @var $repo IRepository
             */
            $repo = SystemContainer::getItem(IStateRepository::class);
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
     * @param IState $item
     */
    protected function checkTransitionsTo(IResponse &$response, IState $item)
    {
        /**
         * @var $transitRepo ITransitionRepository
         * @var $transitionsToState ITransition[]
         */
        $transitRepo = SystemContainer::getItem(ITransitionRepository::class);
        $transitionsToState = $transitRepo->all([
            ITransition::FIELD__STATE_TO => $item->getName()
        ]);
        if (count($transitionsToState)) {
            $response->error('There are transitions to a state', 400);
        }
    }

    /**
     * @param IResponse $response
     * @param IState $item
     */
    protected function checkTransitionsFrom(IResponse &$response, IState $item)
    {
        /**
         * @var $transitRepo ITransitionRepository
         */
        $transitRepo = SystemContainer::getItem(ITransitionRepository::class);
        $transitionsFromState = $transitRepo->all([
            ITransition::FIELD__STATE_FROM => $item->getName()
        ]);
        if (count($transitionsFromState)) {
            $response->error('There are transitions from a state', 400);
        }
    }
}
