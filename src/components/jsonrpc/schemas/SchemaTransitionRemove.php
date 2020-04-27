<?php
namespace extas\components\jsonrpc\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\exceptions\transitions\ExceptionTransitionMissed;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

/**
 * Class SchemaTransitionRemove
 *
 * @stage run.jsonrpc.schema.transition.remove
 * @package extas\components\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class SchemaTransitionRemove extends OperationDispatcher
{
    use TGetSchema;

    protected ?ITransitionRepository $transitionRepo = null;

    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response): void
    {
        $jRpcData = $request->getParams();
        $transitionName = $jRpcData['transition_name'] ?? '';
        $schemaName = $jRpcData['schema_name'] ?? '';

        try {
            $schema = $this->getSchema($schemaName);
            $this->checkTransition($transitionName);

            if ($schema->hasTransitionName($transitionName)) {
                $schema->removeTransitionName($transitionName);
                $this->updateSchema($schema);
                $this->removeTransitionAndDispatchers($transitionName);
            }

            $response->success(['name' => $transitionName]);
        } catch (\Exception $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    /**
     * @param string $transitionName
     */
    protected function removeTransitionAndDispatchers(string $transitionName): void
    {
        $this->transitionRepo->delete([ITransition::FIELD__NAME => $transitionName]);

        /**
         * @var ITransitionDispatcherRepository $dispatchersRepo
         */
        $dispatchersRepo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $dispatchersRepo->delete([ITransitionDispatcher::FIELD__TRANSITION_NAME => $transitionName]);
    }

    /**
     * @param string $transitionName
     * @return mixed
     * @throws ExceptionTransitionMissed
     */
    protected function checkTransition(string $transitionName)
    {
        $this->transitionRepo = SystemContainer::getItem(ITransitionRepository::class);
        $transition = $this->transitionRepo->one([ITransition::FIELD__NAME => $transitionName]);

        if (!$transition) {
            throw new ExceptionTransitionMissed($transitionName);
        }

        return $transition;
    }
}
