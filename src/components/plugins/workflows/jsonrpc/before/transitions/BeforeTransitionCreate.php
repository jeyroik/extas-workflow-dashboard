<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\states\IStateRepository;
use extas\interfaces\workflows\transitions\ITransition;

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
            $item = new Transition($request->getData());
            $this->checkStates($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransition $item
     */
    protected function checkStates(IResponse &$response, ITransition $item)
    {
        $states = [
            $item->getStateFromName(),
            $item->getStateToName()
        ];
        /**
         * @var $repo IStateRepository
         * @var $wStates IState[]
         */
        $repo = SystemContainer::getItem(IStateRepository::class);
        $wStates = $repo->all([IState::FIELD__NAME => $states]);

        if ($item->getStateFromName() == $item->getStateToName()) {
            $response->error('The same state', 400);
        } elseif (count($wStates) != count($states)) {
            $states = array_flip($states);
            foreach ($wStates as $state) {
                unset($states[$state->getName()]);
            }
            $response->error('Unknown states', 400, $states);
        }
    }
}
