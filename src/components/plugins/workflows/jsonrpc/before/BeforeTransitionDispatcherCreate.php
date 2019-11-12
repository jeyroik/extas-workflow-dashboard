<?php
namespace extas\components\plugins\workflows\jsonrpc\before;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeTransitionDispatcherCreate
 *
 * @stage before.run.jsonrpc.transition.dispatcher.create
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDispatcherCreate extends JsonRpcValidationPlugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        if (!$this->isThereError($jRpcData)) {
            $item = new TransitionDispatcher($jRpcData);
            /**
             * @var $repo ITransitionDispatcherRepository
             */
            $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
            if ($repo->one([TransitionDispatcher::FIELD__NAME => $item->getName()])) {
                $this->setResponseError($response, $jRpcData, JsonRpcErrors::ERROR__ALREADY_EXIST);
            } else {
                $this->checkSchema($response, $jRpcData, $item);
                $this->checkTransition($response, $jRpcData, $item);
                $this->checkTemplate($response, $jRpcData, $item);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param ITransitionDispatcher $item
     */
    protected function checkSchema(ResponseInterface &$response, array &$jRpcData, ITransitionDispatcher $item)
    {
        /**
         * @var $repo IWorkflowSchemaRepository
         */
        $repo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $need = $repo->one([IWorkflowSchema::FIELD__NAME => $item->getSchemaName()]);

        if (!$need) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__UNKNOWN_SCHEMA,
                [ITransitionDispatcher::FIELD__SCHEMA_NAME => $item->getSchemaName()]
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param ITransitionDispatcher $item
     */
    protected function checkTransition(ResponseInterface &$response, array &$jRpcData, ITransitionDispatcher $item)
    {
        /**
         * @var $repo IWorkflowTransitionRepository
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $need = $repo->one([IWorkflowTransition::FIELD__NAME => $item->getTransitionName()]);

        if (!$need) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__UNKNOWN_TRANSITION,
                [ITransitionDispatcher::FIELD__TRANSITION_NAME => $item->getTransitionName()]
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param ITransitionDispatcher $item
     */
    protected function checkTemplate(ResponseInterface &$response, array &$jRpcData, ITransitionDispatcher $item)
    {
        /**
         * @var $repo ITransitionDispatcherTemplateRepository
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherTemplateRepository::class);
        $need = $repo->one([ITransitionDispatcherTemplate::FIELD__NAME => $item->getTemplateName()]);

        if (!$need) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__UNKNOWN_TEMPLATE,
                [ITransitionDispatcher::FIELD__TEMPLATE => $item->getTemplateName()]
            );
        }
    }
}
