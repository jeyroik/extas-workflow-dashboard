<?php
namespace extas\components\plugins\workflows\jsonrpc\before;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherTemplate;
use extas\interfaces\IHasName;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeTransitionDispatcherTemplateDelete
 *
 * @stage before.run.jsonrpc.transition.dispatcher.template.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDispatcherTemplateDelete extends JsonRpcValidationPlugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        if (!$this->isThereError($jRpcData)) {
            $response = $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
            $item = new TransitionDispatcherTemplate($jRpcData);
            /**
             * @var $repo IRepository
             */
            $repo = SystemContainer::getItem(ITransitionDispatcherTemplateRepository::class);
            if (!$repo->one([IHasName::FIELD__NAME => $item->getName()])) {
                $this->setResponseError(
                    $response,
                    $jRpcData,
                    JsonRpcErrors::ERROR__UNKNOWN_ENTITY,
                    [TransitionDispatcherTemplate::FIELD__NAME => $item->getName()]
                );
            } else {
                $this->checkTransitionDispatchers($response, $jRpcData, $item);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param ITransitionDispatcherTemplate $item
     */
    protected function checkTransitionDispatchers(
        ResponseInterface &$response,
        array &$jRpcData,
        ITransitionDispatcherTemplate $item
    )
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__TEMPLATE => $item->getName()
        ]);
        if (count($dispatchers)) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__THERE_ARE_DISPATCHERS_BY_TEMPLATE,
                $this->prepare($dispatchers)
            );
        }
    }
}
