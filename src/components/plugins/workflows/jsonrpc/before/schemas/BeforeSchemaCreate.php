<?php
namespace extas\components\plugins\workflows\jsonrpc\before\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\schemas\Schema;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

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
            $item = new Schema($request->getData());
            /**
             * @var $repo ISchemaRepository
             */
            $repo = SystemContainer::getItem(ISchemaRepository::class);
            if ($repo->one([ISchema::FIELD__NAME => $item->getName()])) {
                $response->error('Schema already exist', 400);
            } else {
                $this->checkTransitions($response, $item);
            }
        }
    }

    /**
     * @param IResponse $response
     * @param ISchema $item
     */
    protected function checkTransitions(IResponse &$response, ISchema $item)
    {
        $transitions = $item->getTransitionsNames();
        /**
         * @var ITransitionRepository $repo
         * @var ITransition[] $wTransitions
         */
        $repo = SystemContainer::getItem(ITransitionRepository::class);
        $wTransitions = $repo->all([ITransition::FIELD__NAME => $transitions]);

        if (count($wTransitions) != count($transitions)) {
            $transitions = array_flip($transitions);
            foreach ($wTransitions as $transition) {
                unset($transitions[$transition->getName()]);
            }
            $response->error('Unknown transition', 400, array_keys($transitions));
        }
    }
}
