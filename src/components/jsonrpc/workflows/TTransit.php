<?php
namespace extas\components\jsonrpc\workflows;

use extas\components\SystemContainer;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\Workflow;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\entities\IEntityRepository;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\ResponseInterface;

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
     * @param IRequest $request
     * @throws \Exception
     */
    protected function transit(
        array $contextData,
        array $entityData,
        ITransition $transition,
        IRequest $request
    ): ResponseInterface
    {
        $workflow = new Workflow([Workflow::FIELD__CONTEXT => new EntityContext($contextData)]);
        $result = $workflow->transit($this->buildEntity($entityData), $transition);
        if ($result->hasErrors()) {
            return $this->errorResponse($request->getId(), 'Error entity transition', 400);
        } else {
            return $this->successResponse($request->getId(), $result->getEntity()->__toArray());
        }
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
