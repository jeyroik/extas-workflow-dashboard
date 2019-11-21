<?php
namespace extas\components\plugins\workflows\jsonrpc\before\transitions;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeTransitionUpdate
 *
 * @stage before.run.jsonrpc.transition.update
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionUpdate extends JsonRpcValidationPlugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        if (!$this->isThereError($jRpcData)) {
            $item = new WorkflowTransition($jRpcData['data']);
            /**
             * @var $repo IWorkflowTransitionRepository
             */
            $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
            if ($repo->one([IWorkflowSchema::FIELD__NAME => $item->getName()])) {
                $this->setResponseError($response, $jRpcData, JsonRpcErrors::ERROR__ALREADY_EXIST);
            } else {
                $this->checkStates($response, $jRpcData, $item);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param IWorkflowTransition $item
     */
    protected function checkStates(ResponseInterface &$response, array &$jRpcData, IWorkflowTransition $item)
    {
        $states = [
            $item->getStateFromName(),
            $item->getStateToName()
        ];
        /**
         * @var $repo IWorkflowStateRepository
         * @var $wStates IWorkflowState[]
         */
        $repo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $wStates = $repo->all([IWorkflowState::FIELD__NAME => $states]);

        if ($item->getStateFromName() == $item->getStateToName()) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__THE_SAME_STATE,
                [
                    IWorkflowState::FIELD__NAME => $item->getStateToName()
                ]
            );
        } elseif (count($wStates) != count($states)) {
            $states = array_flip($states);
            foreach ($wStates as $state) {
                unset($states[$state->getName()]);
            }
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__UNKNOWN_STATES,
                array_keys($states)
            );
        }
    }
}
