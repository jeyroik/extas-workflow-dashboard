<?php
namespace extas\components\jsonrpc\schemas;

use extas\components\exceptions\AlreadyExist;
use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionSample;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SchemaTransitionAdd
 *
 * @deprecated use workflow.transition.create
 *
 * @jsonrpc_operation
 * @jsonrpc_name workflow.schema.transition.add
 * @jsonrpc_title Add transition to a schema
 * @jsonrpc_description This method is deprecated! Use workflow.transition.create
 * @jsonrpc_request_field schema_name:string
 * @jsonrpc_request_field transition_name:string
 * @jsonrpc_request_field transition_sample_name:string
 * @jsonrpc_request_field dispatchers:array
 * @jsonrpc_response_field name:string
 *
 * @method IRepository workflowTransitionsDispatchers()
 * @method IRepository workflowTransitions()
 * @method IRepository workflowTransitionsSamples()
 *
 * @stage run.jsonrpc.schema.transition.add
 * @package extas\components\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class SchemaTransitionAdd extends OperationDispatcher
{
    use TGetSchema;

    public function __invoke(): ResponseInterface
    {
        $request = $this->getJsonRpcRequest();
        $jRpcData = $request->getParams();
        $transitionSampleName = $jRpcData['transition_sample_name'] ?? '';
        $schemaName = $jRpcData['schema_name'] ?? '';
        $dispatchersData = $jRpcData['dispatchers'] ?? [];

        try {
            $schema = $this->getSchema($schemaName);

            if ($this->hasTransitionByThisSample($schemaName, $transitionSampleName)) {
                throw new AlreadyExist('Transition by this sample');
            }

            $transition = $schema->addTransition($transitionSampleName);
            $this->updateSchema($schema);
            $this->createDispatchers($dispatchersData, $transition->getName());
            return $this->successResponse($request->getId(), ['name' => $transition->getName()]);
        } catch (\Exception $e) {
            return $this->errorResponse($request->getId(), $e->getMessage(), 400);
        }
    }

    /**
     * @param string $schemaName
     * @param string $transitionSampleName
     * @return bool
     */
    protected function hasTransitionByThisSample(string $schemaName, string $transitionSampleName): bool
    {
        $transition = $this->workflowTransitions()->one([
            ITransition::FIELD__SCHEMA_NAME => $schemaName,
            ITransition::FIELD__SAMPLE_NAME => $transitionSampleName
        ]);

        return $transition ? true : false;
    }

    /**
     * @param array $dispatchersData
     * @param string $transitionName
     */
    protected function createDispatchers(array $dispatchersData, string $transitionName): void
    {
        $dispatcherRepo = $this->workflowTransitionsDispatchers();

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

        $exits = $this->workflowTransitions()->one([ITransition::FIELD__NAME => $transitionName]);

        if ($exits) {
            throw new \Exception('Transition already exists');
        }

        return $this->workflowTransitions()->create($transition);
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.workflow.schema.transition.add';
    }
}
