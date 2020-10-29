<?php
namespace extas\components\plugins\workflows\schemas;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\plugins\Plugin;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageAfterJsonRpcOperation;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeSchemaDelete
 *
 * @method IRepository workflowTransitions()
 * @method IRepository workflowTransitionsDispatchers()
 * @method IRepository workflowStates()
 * @method IRepository workflowEntities()
 *
 * @package extas\components\plugins\workflows\schemas
 * @author jeyroik <jeyroik@gmail.com>
 */
class AfterSchemaDelete extends Plugin implements IStageAfterJsonRpcOperation
{
    use THasJsonRpcRequest;
    use THasJsonRpcResponse;

    /**
     * @param IJsonRpcOperation $operation
     * @param string $endpoint
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(
        IJsonRpcOperation $operation,
        string $endpoint,
        ResponseInterface $response
    ): ResponseInterface
    {
        if ($operation->getName() === 'workflow.schema.delete') {
            $request = $this->getJsonRpcRequest();
            $data = $request->getData([]);
            $schemaName = $data[ISchema::FIELD__NAME] ?? '';

            $this->deleteTransitions($schemaName);
            $this->deleteStates($schemaName);
            $this->deleteEntity($schemaName);
        }

        return $response;
    }

    /**
     * @param string $schemaName
     */
    protected function deleteTransitions(string $schemaName): void
    {
        $transitions = $this->workflowTransitions()->all([
            ITransition::FIELD__SCHEMA_NAME => $schemaName
        ]);

        $names = array_column($transitions, ITransition::FIELD__NAME);

        $this->workflowTransitionsDispatchers()->delete([
            ITransitionDispatcher::FIELD__TRANSITION_NAME => $names
        ]);

        $this->workflowTransitions()->delete([
            ITransition::FIELD__SCHEMA_NAME => $schemaName
        ]);
    }

    /**
     * @param string $schemaName
     */
    protected function deleteStates(string $schemaName): void
    {
        $this->workflowStates()->delete([
            IState::FIELD__SCHEMA_NAME => $schemaName
        ]);
    }

    /**
     * @param string $schemaName
     */
    protected function deleteEntity(string $schemaName): void
    {
        $this->workflowEntities()->delete([
            IEntity::FIELD__SCHEMA_NAME => $schemaName
        ]);
    }
}
