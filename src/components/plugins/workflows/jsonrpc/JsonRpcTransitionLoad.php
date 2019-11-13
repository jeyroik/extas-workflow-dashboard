<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcTransitionLoad
 *
 * @stage run.jsonrpc.transition.load
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcTransitionLoad extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $transitions = $jRpcData['data'];
        $transitionsNames = array_column($transitions, IWorkflowTransition::FIELD__NAME);
        $transitionsByName = array_column($transitions, null, IWorkflowTransition::FIELD__NAME);

        /**
         * @var $repo IWorkflowTransitionRepository
         * @var $existed IWorkflowTransition[]
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $existed = $repo->all([IWorkflowTransition::FIELD__NAME => $transitionsNames]);

        foreach ($existed as $existing) {
            if (isset($transitionsByName[$existing->getName()])) {
                unset($transitionsByName[$existing->getName()]);
            }
        }

        $fromStates = array_column($transitions, IWorkflowTransition::FIELD__STATE_FROM);
        $toStates = array_column($transitions, IWorkflowTransition::FIELD__STATE_TO);
        $statesNames = array_merge($fromStates, $toStates);

        /**
         * @var $stateRepo IWorkflowStateRepository
         * @var $existed IWorkflowState[]
         */
        $stateRepo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $existed = $stateRepo->all([IWorkflowState::FIELD__NAME => $statesNames]);
        $missedNames = [];

        if (count($existed) != count($statesNames)) {
            $existedNames = [];
            foreach ($existed as $existing) {
                $existedNames[] = $existing->getName();
            }
            $missedNames = array_diff($statesNames, $existedNames);
            $missedNames = array_flip($missedNames);
        }

        $created = 0;

        foreach ($transitionsByName as $data) {
            $item = new WorkflowTransition($data);
            if (!isset($missedNames[$item->getStateFromName()]) && !isset($missedNames[$item->getStateToName()])) {
                $repo->create($item);
                $created++;
            }
        }

        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => [
                    'created_count' => $created,
                    'got_count' => count($transitions)
                ]
            ]));
    }
}
