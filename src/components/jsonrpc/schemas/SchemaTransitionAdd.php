<?php
namespace extas\components\jsonrpc\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionSample;
use extas\interfaces\workflows\transitions\ITransitionSampleRepository;

/**
 * Class SchemaTransitionAdd
 *
 * @stage run.jsonrpc.schema.transition.add
 * @package extas\components\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class SchemaTransitionAdd extends OperationDispatcher
{
    use TGetSchema;

    /**
     * @param IRequest $request
     * @param IResponse $response
     */
    protected function dispatch(IRequest $request, IResponse &$response)
    {
        $jRpcData = $request->getParams();
        $transitionName = $jRpcData['transition_name'] ?? '';
        $transitionSampleName = $jRpcData['transition_sample_name'] ?? '';
        $schemaName = $jRpcData['schema_name'] ?? '';
        $dispatchersData = $jRpcData['dispatchers'] ?? [];

        try {
            $schema = $this->getSchema($schemaName);
            $sample = $this->getTransitionSample($transitionSampleName);

            if ($schema->hasTransitionName($transitionName)) {
                throw new \Exception('Schema has already this transition');
            }

            $transition = $this->createTransition($sample, $schemaName, $transitionName);
            $schema->addTransitionName($transition->getName());
            $this->updateSchema($schema);
            $this->createDispatchers($dispatchersData, $transitionName);
            $response->success(['name' => $transitionName]);
        } catch (\Exception $e) {
            $response->error($e->getMessage(), 400);
        }
    }

    /**
     * @param array $dispatchersData
     * @param string $transitionName
     */
    protected function createDispatchers(array $dispatchersData, string $transitionName): void
    {
        $dispatcherRepo = SystemContainer::getItem(ITransitionDispatcherRepository::class);

        foreach ($dispatchersData as $dispatchersDatum) {
            $dispatchersDatum[ITransitionDispatcher::FIELD__TRANSITION_NAME] = $transitionName;
            $dispatcher = new TransitionDispatcher($dispatchersDatum);
            $dispatcherRepo->create($dispatcher);
        }
    }

    /**
     * @param ITransitionSample $sample
     * @param string $schemaName
     * @param string $transitionName
     * @return ITransition
     * @throws \Exception
     */
    protected function createTransition(
        ITransitionSample $sample,
        string $schemaName,
        string $transitionName
    ): ITransition
    {
        $transition = new Transition();
        $transition->buildFromSample($sample)
            ->setSchemaName($schemaName)
            ->setName($transitionName);

        /**
         * @var ITransitionRepository $repo
         */
        $repo = SystemContainer::getItem(ITransitionRepository::class);
        $exits = $repo->one([ITransition::FIELD__NAME => $transitionName]);

        if ($exits) {
            throw new \Exception('Transition already exists');
        }

        return $repo->create($transition);
    }

    /**
     * @param string $name
     * @return ITransitionSample
     * @throws \Exception
     */
    protected function getTransitionSample(string $name): ITransitionSample
    {
        /**
         * @var $transitionSampleRepo ITransitionSampleRepository
         */
        $transitionSampleRepo = SystemContainer::getItem(ITransitionSampleRepository::class);
        $sample = $transitionSampleRepo->one([ITransitionSample::FIELD__NAME => $name]);

        if (!$sample) {
            throw new \Exception('Missed transition sample');
        }

        return $sample;
    }
}
