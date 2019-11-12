<?php
namespace extas\components\plugins\workflows\jsonrpc\before;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeSchemaCreate
 *
 * @stage before.run.jsonrpc.schema.create
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeSchemaCreate extends JsonRpcValidationPlugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        if (!$this->isThereError($jRpcData)) {
            $item = new WorkflowSchema($jRpcData);
            /**
             * @var $repo IWorkflowSchemaRepository
             */
            $repo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
            if ($repo->one([IWorkflowSchema::FIELD__NAME => $item->getName()])) {
                $this->setResponseError($response, $jRpcData, JsonRpcErrors::ERROR__ALREADY_EXIST);
            } else {
                $this->checkStates($response, $jRpcData, $item);
                $this->checkTransitions($response, $jRpcData, $item);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param IWorkflowSchema $item
     */
    protected function checkStates(ResponseInterface &$response, array &$jRpcData, IWorkflowSchema $item)
    {
        $states = $item->getStatesNames();
        /**
         * @var $repo IWorkflowStateRepository
         * @var $wStates IWorkflowState[]
         */
        $repo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $wStates = $repo->all([IWorkflowState::FIELD__NAME => $states]);

        if (count($wStates) != count($states)) {
            $states = array_flip($states);
            foreach ($wStates as $state) {
                unset($states[$state->getName()]);
            }
            $this->setResponseError($response, $jRpcData, JsonRpcErrors::ERROR__UNKNOWN_STATES, $states);
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param IWorkflowSchema $item
     */
    protected function checkTransitions(ResponseInterface &$response, array &$jRpcData, IWorkflowSchema $item)
    {
        $transitions = $item->getTransitionsNames();
        /**
         * @var $repo IWorkflowTransitionRepository
         * @var $wTransitions IWorkflowTransition[]
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $wTransitions = $repo->all([IWorkflowTransition::FIELD__NAME => $transitions]);

        if (count($wTransitions) != count($transitions)) {
            $transitions = array_flip($transitions);
            foreach ($wTransitions as $transition) {
                unset($transitions[$transition->getName()]);
            }
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__UNKNOWN_TRANSITION,
                $transitions
            );
        }
    }
}
