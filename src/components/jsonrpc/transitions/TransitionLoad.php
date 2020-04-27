<?php
namespace extas\components\jsonrpc\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\transitions\ITransition;
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
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $transitions = $request->getData();
        $transitionsNames = array_column($transitions, ITransition::FIELD__NAME);
        $transitionsByName = array_column($transitions, null, ITransition::FIELD__NAME);

        /**
         * @var $repo ITransitionRepository
         * @var $existed ITransition[]
         */
        $repo = SystemContainer::getItem(ITransitionRepository::class);
        $existed = $repo->all([ITransition::FIELD__NAME => $transitionsNames]);
        $existedNames = [];
        foreach ($existed as $item) {
            $existedNames[$item->getName()] = true;
        }

        $transitionsForCreating = array_intersect_key($transitionsByName, $existedNames);
        $created = 0;

        foreach ($transitionsForCreating as $data) {
            $item = new Transition($data);
            $repo->create($item);
            $created++;
        }

        $response->success([
            'created_count' => $created,
            'got_count' => count($transitions)
        ]);
    }
}
