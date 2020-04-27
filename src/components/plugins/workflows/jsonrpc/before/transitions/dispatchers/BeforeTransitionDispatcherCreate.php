<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions\dispatchers;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherSample;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherSampleRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

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
         * @var $repo ISchemaRepository
         */
        $repo = SystemContainer::getItem(ISchemaRepository::class);
        $need = $repo->one([ISchema::FIELD__NAME => $item->getSchemaName()]);

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
         * @var $repo ITransitionRepository
         */
        $repo = SystemContainer::getItem(ITransitionRepository::class);
        $need = $repo->one([ITransition::FIELD__NAME => $item->getTransitionName()]);

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
         * @var $repo ITransitionDispatcherSampleRepository
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherSampleRepository::class);
        $need = $repo->one([ITransitionDispatcherSample::FIELD__NAME => $item->getTemplateName()]);

        if (!$need) {
            $response->error('Unknown template', 400);
        }
    }
}
