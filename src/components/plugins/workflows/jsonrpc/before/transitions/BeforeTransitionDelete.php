<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;
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
            $this->checkSchemas($response, $item);
            $this->checkTransitionDispatchers($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransition $item
     */
    protected function checkSchemas(IResponse &$response, ITransition $item)
    {
        /**
         * @var $repo ISchemaRepository
         * @var $schemas ISchema[]
         */
        $repo = SystemContainer::getItem(ISchemaRepository::class);
        $schemas = $repo->all([
            ISchema::FIELD__TRANSITIONS => $item->getName()
        ]);
        if (count($schemas)) {
            $response->error('There are schemas with a transition', 400);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransition $item
     */
    protected function checkTransitionDispatchers(IResponse &$response, ITransition $item)
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__TRANSITION_NAME => $item->getName()
        ]);
        if (count($dispatchers)) {
            $response->error('There are dispatchers for a transition', 400);
        }
    }
}
