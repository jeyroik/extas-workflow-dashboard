<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions\dispatchers\templates;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSample;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherSample;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherSampleRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;

/**
 * Class BeforeTransitionDispatcherTemplateDelete
 *
 * @stage before.run.jsonrpc.transition.dispatcher.template.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDispatcherTemplateDelete extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new TransitionDispatcherSample($request->getData());
            $this->checkTransitionDispatchers($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransitionDispatcherSample $item
     */
    protected function checkTransitionDispatchers(IResponse &$response, ITransitionDispatcherSample $item)
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__SAMPLE_NAME => $item->getName()
        ]);
        if (count($dispatchers)) {
            $response->error('There are dispatchers with this template', 400);
        }
    }
}
