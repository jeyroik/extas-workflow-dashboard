<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\exceptions\MissedOrUnknown;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\Workflow;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transits\ITransitResult;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait TTransit
 *
 * @method IRepository workflowEntities()
 *
 * @package extas\components\jsonrpc\workflows
 * @author jeyroik@gmail.com
 */
trait TTransit
{
    /**
     * @param array $contextData
     * @param array $entityData
     * @param ITransition $transition
     * @return array
     * @throws \Exception
     */
    protected function transit(
        array $contextData,
        array $entityData,
        ITransition $transition
    ): array
    {
        $workflow = new Workflow([Workflow::FIELD__CONTEXT => new EntityContext($contextData)]);
        $result = $workflow->transit($this->buildEntity($entityData), $transition);
        if ($result->hasErrors()) {
            throw new \Exception('Error entity transition.' . $this->getResultErrors($result), 400);
        } else {
            return $result->getEntity()->__toArray();
        }
    }

    /**
     * @param ITransitResult $result
     * @return string
     */
    protected function getResultErrors(ITransitResult $result): string
    {
        $errors = '';
        $resultErrors = $result->getErrors();
        foreach ($resultErrors as $error) {
            $errors .= $error->getTitle() . ': ' . $error->getDescription() . ';';
        }

        return $errors;
    }

    /**
     * @param array $entityData
     * @return IEntity
     * @throws MissedOrUnknown
     */
    protected function buildEntity(array $entityData): IEntity
    {
        /**
         * @var IEntity $entityFormal
         */
        $entity = new Entity($entityData);
        $entityFormal = $this->workflowEntities()->one([IEntity::FIELD__NAME => $entity->getName()]);

        if (!$entityFormal) {
            throw new MissedOrUnknown('entity');
        }

        if (!$entity->has(...$entityFormal->getParametersNames())) {
            throw new MissedOrUnknown('entity parameters');
        }

        return $entity;
    }
}
