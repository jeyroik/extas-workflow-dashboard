<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions\dispatchers;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

/**
 * Class BeforeTransitionDispatcherCreate
 *
 * @stage before.run.jsonrpc.transition.dispatcher.create
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDispatcherCreate extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new TransitionDispatcher($request->getData());
            $this->checkSchema($response, $item);
            $this->checkTransition($response, $item);
            $this->checkTemplate($response, $item);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransitionDispatcher $item
     */
    protected function checkSchema(IResponse &$response, ITransitionDispatcher $item)
    {
        /**
         * @var $repo IWorkflowSchemaRepository
         */
        $repo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $need = $repo->one([IWorkflowSchema::FIELD__NAME => $item->getSchemaName()]);

        if (!$need) {
            $response->error('Unknown schema', 400);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransitionDispatcher $item
     */
    protected function checkTransition(IResponse &$response, ITransitionDispatcher $item)
    {
        /**
         * @var $repo IWorkflowTransitionRepository
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $need = $repo->one([IWorkflowTransition::FIELD__NAME => $item->getTransitionName()]);

        if (!$need) {
            $response->error('Unknown transition', 400);
        }
    }

    /**
     * @param IResponse $response
     * @param ITransitionDispatcher $item
     */
    protected function checkTemplate(IResponse &$response, ITransitionDispatcher $item)
    {
        /**
         * @var $repo ITransitionDispatcherTemplateRepository
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherTemplateRepository::class);
        $need = $repo->one([ITransitionDispatcherTemplate::FIELD__NAME => $item->getTemplateName()]);

        if (!$need) {
            $response->error('Unknown template', 400);
        }
    }
}
