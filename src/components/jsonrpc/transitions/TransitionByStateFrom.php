<?php
namespace extas\components\jsonrpc\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\jsonrpc\schemas\TGetSchema;
use extas\components\SystemContainer;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\transits\TransitResult;
use extas\interfaces\IItem;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TransitionByStateFrom
 *
 * @deprecated use workflow.transition.index with filter: {state_from: {eq:<state.name>}}
 *
 * @jsonrpc_operation
 * @jsonrpc_name workflow.transition.by_state_from.index
 * @jsonrpc_title Transiiton index by state from
 * @jsonrpc_description Get transitions list by state from
 * @jsonrpc_request_field schema_name:string
 * @jsonrpc_request_field state_name:string
 * @jsonrpc_request_field entity:object
 * @jsonrpc_request_field context:object
 * @jsonrpc_response_field name:string
 * @jsonrpc_response_field title:string
 * @jsonrpc_response_field description:string
 * @jsonrpc_response_field state_from:string
 * @jsonrpc_response_field state_to:string
 *
 * @stage run.jsonrpc.transition.by_state_from.index
 * @package extas\components\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class TransitionByStateFrom extends OperationDispatcher
{
    use TGetSchema;

    /**
     * @return ResponseInterface
     */
    public function __invoke(): ResponseInterface
    {
        $request = $this->convertPsrToJsonRpcRequest();
        $jRpcData = $request->getParams();

        try {
            $schema = $this->getSchema($jRpcData['schema_name'] ?? '');
            $state = $jRpcData['state_name'];
            $entity = new Entity($jRpcData['entity'] ?? []);
            $transitions = $this->getTransitions($state, $schema);
            $result = [];
            $context = new EntityContext($jRpcData['context'] ?? []);

            foreach ($transitions as $transition) {
                $this->addValid($transition, $entity, $context, $result);
            }

            $filter = $request->getFilter();
            $filterNames = isset($filter['transition_name'], $filter['transition_name']['$in'])
                ? array_flip($filter['transition_name']['$in'])
                : [];

            $result = array_intersect_key($result, $filterNames);
            return $this->successResponse($request->getId(), array_values($result));
        } catch (\Exception $e) {
            return $this->errorResponse($request->getId(), $e->getMessage(), 400);
        }
    }

    /**
     * @param ITransition $transition
     * @param IEntity $entity
     * @param IItem $context
     * @param array $result
     */
    protected function addValid($transition, $entity, $context, array &$result): void
    {
        $transitResult = new TransitResult();
        $conditions = $transition->getConditions();

        $valid = true;
        foreach ($conditions as $condition) {
            if (!$condition->dispatch($context, $transitResult, $entity)) {
                $valid = false;
            }
        }

        $valid && ($result[$transition->getName()] = $transition->__toArray());
    }

    /**
     * @param string $stateName
     * @param ISchema $schema
     *
     * @return array|ITransition[]
     */
    protected function getTransitions(string $stateName, ISchema $schema): array
    {
        /**
         * @var $repo ITransitionRepository
         * @var $transitions ITransition[]
         */
        $repo = SystemContainer::getItem(ITransitionRepository::class);

        return $repo->all([
            ITransition::FIELD__NAME => $schema->getTransitionsNames(),
            ITransition::FIELD__STATE_FROM => $stateName
        ]);
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.workflow.transition.by_state_from';
    }
}
