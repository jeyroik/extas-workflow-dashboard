<?php
namespace extas\components\plugins\workflows\jsonrpc\entities;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\entities\WorkflowEntityContext;
use extas\components\workflows\Workflow;
use extas\interfaces\workflows\entities\IWorkflowEntity;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\errors\ITransitionError;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcEntityTransit
 *
 * @stage run.jsonrpc.entity.run
 * @package extas\components\plugins\workflows\jsonrpc\states
 * @author jeyroik@gmail.com
 */
class JsonRpcEntityTransit extends JsonRpcValidationPlugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $entityData = $jRpcData['entity'] ?? [];
        $context = $jRpcData['context'] ?? [];
        $schemaName = $jRpcData['schema_name'] ?? '';
        $transitionName = $jRpcData['transition_name'] ?? '';

        /**
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schema IWorkflowSchema
         * @var $transitionRepo IWorkflowTransitionRepository
         * @var $transition IWorkflowTransition
         * @var $entity IWorkflowEntity
         */
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schema = $schemaRepo->one([IWorkflowSchema::FIELD__NAME => $schemaName]);

        if (!$schema) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__UNKNOWN_SCHEMA,
                [IWorkflowSchema::FIELD__NAME => $schemaName]
            );
        } else {
            $entityTemplate = $schema->getEntityTemplate();
            $entity = $entityTemplate->buildClassWithParameters($entityData);
            $entity->setTemplateName($entityTemplate->getName());

            $transitionRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
            $transition = $transitionRepo->one([IWorkflowTransition::FIELD__NAME => $transitionName]);

            if (!$transition) {
                $this->setResponseError(
                    $response,
                    $jRpcData,
                    JsonRpcErrors::ERROR__UNKNOWN_TRANSITION,
                    [IWorkflowTransition::FIELD__NAME => $transitionName]
                );
            } else {
                $this->transit($entity, $context, $transition, $schema, $response, $jRpcData);
            }
        }
    }

    /**
     * @param IWorkflowEntity $entity
     * @param array $context
     * @param IWorkflowTransition $transition
     * @param IWorkflowSchema $schema
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    protected function transit(
        IWorkflowEntity $entity,
        array $context,
        IWorkflowTransition $transition,
        IWorkflowSchema $schema,
        ResponseInterface &$response,
        array $jRpcData
    )
    {
        if ($transition->getStateFromName() != $entity->getStateName()) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__CAN_NOT_TRANSIT_ENTITY_TRANSITION,
                [
                    'transition' => $transition->__toArray(),
                    'entity' => $entity->__toArray()
                ]
            );
        } else {
            $result = Workflow::transitByTransition(
                $entity,
                $transition->getName(),
                $schema,
                new WorkflowEntityContext($context)
            );
            if (!$result->isSuccess()) {
                $this->setError($response, $result->getError());
            } else {
                $this->setSuccess($response, $entity);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param ITransitionError $error
     */
    protected function setError(ResponseInterface &$response, ITransitionError $error)
    {
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'error' => $error->__toArray()
            ]));
    }

    /**
     * @param ResponseInterface $response
     * @param IWorkflowEntity $entity
     */
    protected function setSuccess(ResponseInterface &$response, IWorkflowEntity $entity)
    {
        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => $entity->__toArray()
            ]));
    }
}
