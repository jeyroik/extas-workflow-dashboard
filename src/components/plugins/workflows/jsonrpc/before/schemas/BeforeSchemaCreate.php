<?php
namespace extas\components\plugins\workflows\jsonrpc\before\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

/**
 * Class BeforeSchemaCreate
 *
 * @stage before.run.jsonrpc.schema.create
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeSchemaCreate extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $item = new WorkflowSchema($request->getData());
            /**
             * @var $repo IWorkflowSchemaRepository
             */
            $repo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
            if ($repo->one([IWorkflowSchema::FIELD__NAME => $item->getName()])) {
                $response->error('Schema already exist', 400);
            } else {
                $this->checkTransitions($response, $item);
            }
        }
    }

    /**
     * @param IResponse $response
     * @param IWorkflowSchema $item
     */
    protected function checkTransitions(IResponse &$response, IWorkflowSchema $item)
    {
        $transitions = $item->getTransitionsNames();
        /**
         * @var IWorkflowTransitionRepository $repo
         * @var IWorkflowTransition[] $wTransitions
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $wTransitions = $repo->all([IWorkflowTransition::FIELD__NAME => $transitions]);

        if (count($wTransitions) != count($transitions)) {
            $transitions = array_flip($transitions);
            foreach ($wTransitions as $transition) {
                unset($transitions[$transition->getName()]);
            }
            $response->error('Unknown transition', 400);
        }
    }
}
