<?php
namespace extas\components\jsonrpc\schemas;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\workflows\transitions\Transition;
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
 * @method workflowTransitionDispatcherRepository()
 * @method workflowTransitionRepository()
 * @method workflowTransitionSampleRepository()
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
        $request = $this->convertPsrToJsonRpcRequest();
        $jRpcData = $request->getParams();
        $transitionName = $jRpcData['transition_name'] ?? '';
        $transitionSampleName = $jRpcData['transition_sample_name'] ?? '';
        $schemaName = $jRpcData['schema_name'] ?? '';
        $dispatchersData = $jRpcData['dispatchers'] ?? [];

        try {
            $schema = $this->getSchema($schemaName);

            if ($schema->hasTransition($transitionName)) {
                throw new \Exception('Schema has already this transition');
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
     * @param array $dispatchersData
     * @param string $transitionName
     */
    protected function createDispatchers(array $dispatchersData, string $transitionName): void
    {
        $dispatcherRepo = $this->workflowTransitionDispatcherRepository();

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

        $exits = $this->workflowTransitionRepository()->one([ITransition::FIELD__NAME => $transitionName]);

        if ($exits) {
            throw new \Exception('Transition already exists');
        }

        return $this->workflowTransitionRepository()->create($transition);
    }

    /**
     * @param string $name
     * @return ITransitionSample
     * @throws \Exception
     */
    protected function getTransitionSample(string $name): ITransitionSample
    {
        $sample = $this->workflowTransitionSampleRepository()->one([ITransitionSample::FIELD__NAME => $name]);

        if (!$sample) {
            throw new \Exception('Missed transition sample');
        }

        return $sample;
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.workflow.schema.transition.add';
    }
}
