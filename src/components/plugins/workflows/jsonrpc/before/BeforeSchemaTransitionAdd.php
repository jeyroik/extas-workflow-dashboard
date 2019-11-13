<?php
namespace extas\components\plugins\workflows\jsonrpc\before;

use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeSchemaTransitionAdd
 *
 * @stage before.run.jsonrpc.schema.transition.add
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeSchemaTransitionAdd extends JsonRpcValidationPlugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        if (!$this->isThereError($jRpcData)) {

            $this->checkTransition($response, $jRpcData);
            $this->checkSchema($response, $jRpcData);
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    protected function checkTransition(ResponseInterface &$response, array &$jRpcData)
    {
        $transitionName = $jRpcData['transition'] ?? '';
        /**
         * @var $transitRepo IWorkflowTransitionRepository
         */
        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transition = $transitRepo->one([IWorkflowTransition::FIELD__NAME => $transitionName]);

        if (!$transition) {
            $this->setResponseError($response, $jRpcData, 10602);
        }
    }


    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    protected function checkSchema(ResponseInterface &$response, array &$jRpcData)
    {
        $schemaName = $jRpcData['schema_name'] ?? '';
        /**
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schema IWorkflowSchema
         */
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schema = $schemaRepo->one([IWorkflowSchema::FIELD__NAME => $schemaName]);
        if (!$schema) {
            $this->setResponseError($response, $jRpcData, 10701);
        }
    }
}
