<?php
namespace extas\components\plugins\workflows\jsonrpc\before\states;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\IHasName;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeStateDelete
 *
 * @stage before.run.jsonrpc.state.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeStateDelete extends JsonRpcValidationPlugin
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
            $item = new WorkflowState($jRpcData);
            /**
             * @var $repo IRepository
             */
            $repo = SystemContainer::getItem(IWorkflowStateRepository::class);
            if (!$repo->one([IHasName::FIELD__NAME => $item->getName()])) {
                $this->setResponseError(
                    $response,
                    $jRpcData,
                    JsonRpcErrors::ERROR__UNKNOWN_ENTITY,
                    [WorkflowState::FIELD__NAME => $item->getName()]
                );
            } else {
                $this->checkTransitionsTo($response, $jRpcData, $item);
                $this->checkTransitionsFrom($response, $jRpcData, $item);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param IWorkflowState $item
     */
    protected function checkTransitionsTo(ResponseInterface &$response, array &$jRpcData, IWorkflowState $item)
    {
        /**
         * @var $transitRepo IWorkflowTransitionRepository
         * @var $transitionsToState IWorkflowTransition[]
         */
        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transitionsToState = $transitRepo->all([
            IWorkflowTransition::FIELD__STATE_TO => $item->getName()
        ]);
        if (count($transitionsToState)) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__THERE_ARE_TRANSITIONS_TO_STATE,
                $this->prepare($transitionsToState)
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param IWorkflowState $item
     */
    protected function checkTransitionsFrom(ResponseInterface &$response, array &$jRpcData, IWorkflowState $item)
    {
        /**
         * @var $transitRepo IWorkflowTransitionRepository
         */
        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transitionsFromState = $transitRepo->all([
            IWorkflowTransition::FIELD__STATE_FROM => $item->getName()
        ]);
        if (count($transitionsFromState)) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__THERE_ARE_TRANSITIONS_FROM_STATE,
                $this->prepare($transitionsFromState)
            );
        }
    }
}
