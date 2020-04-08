<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions\dispatchers\templates;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplate;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

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
            $item = new TransitionDispatcherTemplate($request->getData());
            $this->checkTransitionDispatchers($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransitionDispatcherTemplate $item
     */
    protected function checkTransitionDispatchers(IResponse &$response, ITransitionDispatcherTemplate $item)
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__TEMPLATE => $item->getName()
        ]);
        if (count($dispatchers)) {
            $response->error('There are dispatchers with this template', 400);
        }
    }
}
