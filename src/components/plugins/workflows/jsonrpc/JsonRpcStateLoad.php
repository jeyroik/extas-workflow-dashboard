<?php
namespace extas\components\plugins\workflows\jsonrpc;

use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcStateLoad
 *
 * @stage run.jsonrpc.state.load
 * @package extas\components\plugins\workflows\jsonrpc
 * @author jeyroik@gmail.com
 */
class JsonRpcStateLoad extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $states = $jRpcData['data'];
        $statesNames = array_column($states, IWorkflowState::FIELD__NAME);
        $statesByName = array_column($states, null, IWorkflowState::FIELD__NAME);

        /**
         * @var $stateRepo IWorkflowStateRepository
         * @var $existed IWorkflowState[]
         */
        $stateRepo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $existed = $stateRepo->all([IWorkflowState::FIELD__NAME => $statesNames]);

        foreach ($existed as $existingState) {
            if (isset($statesByName[$existingState->getName()])) {
                unset($statesByName[$existingState->getName()]);
            }
        }

        $created = 0;

        foreach ($statesByName as $stateData) {
            $state = new WorkflowState($stateData);
            $stateRepo->create($state);
            $created++;
        }

        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => ['count' => $created]
            ]));
    }
}
