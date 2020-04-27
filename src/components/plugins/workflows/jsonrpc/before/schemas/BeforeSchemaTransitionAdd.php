<?php
namespace extas\components\plugins\workflows\jsonrpc\before\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

/**
 * Class BeforeSchemaTransitionAdd
 *
 * @stage before.run.jsonrpc.schema.transition.add
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeSchemaTransitionAdd extends OperationDispatcher
{
    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        if (!$response->hasError()) {
            $this->checkTransition($response, $request->getParams());
            $this->checkSchema($response, $request->getParams());
        }
    }

    /**
     * @param IResponse $response
     * @param array $jRpcData
     */
    protected function checkTransition(IResponse &$response, array $jRpcData)
    {
        $transitionName = $jRpcData['transition_name'] ?? '';
        /**
         * @var $transitRepo ITransitionRepository
         */
        $transitRepo = SystemContainer::getItem(ITransitionRepository::class);
        $transition = $transitRepo->one([ITransition::FIELD__NAME => $transitionName]);

        if (!$transition) {
            $response->error('Unknown transition', 400);
        }
    }


    /**
     * @param IResponse $response
     * @param array $jRpcData
     */
    protected function checkSchema(IResponse &$response, array $jRpcData)
    {
        $schemaName = $jRpcData['schema_name'] ?? '';
        /**
         * @var $schemaRepo ISchemaRepository
         * @var $schema ISchema
         */
        $schemaRepo = SystemContainer::getItem(ISchemaRepository::class);
        $schema = $schemaRepo->one([ISchema::FIELD__NAME => $schemaName]);
        if (!$schema) {
            $response->error('Unknown schema', 400);
        }
    }
}
