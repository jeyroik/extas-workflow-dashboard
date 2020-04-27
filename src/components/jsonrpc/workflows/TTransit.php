<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\SystemContainer;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\Workflow;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\entities\IEntityRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transits\ITransitResult;

/**
 * Trait TTransit
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
     * @param IResponse $response
     * @throws \Exception
     */
    protected function transit(array $contextData, array $entityData, ITransition $transition, IResponse &$response)
    {
        $workflow = new Workflow([Workflow::FIELD__CONTEXT => new EntityContext($contextData)]);
        $result = $workflow->transit($this->buildEntity($entityData), $transition);
        if ($result->hasErrors()) {
            $this->setError($result, $response);
        } else {
            $response->success($result->getEntity()->__toArray());
        }
    }

    /**
     * @param ITransitResult $result
     * @param IResponse $response
     */
    protected function setError(ITransitResult $result, IResponse &$response): void
    {
        $errorsMessages = [];
        $errors = $result->getErrors();
        foreach ($errors as $error) {
            $errorsMessages[] = (string) $error;
        }

        $response->error(
            'Error while entity transiting',
            400,
            $errorsMessages
        );
    }

    /**
     * @param array $entityData
     * @return IEntity
     * @throws
     */
    protected function buildEntity(array $entityData): IEntity
    {
        $entity = new Entity($entityData);
        /**
         * @var IEntityRepository $repo
         * @var IEntity $entityFormal
         */
        $repo = SystemContainer::getItem(IEntityRepository::class);
        $entityFormal = $repo->one([IEntity::FIELD__NAME => $entity->getName()]);

        if (!$entityFormal) {
            throw new \Exception('Missed entity');
        }

        if (!$entity->has(...$entityFormal->getParametersNames())) {
            throw new \Exception('Missed entity parameters');
        }

        return $entity;
    }
}
