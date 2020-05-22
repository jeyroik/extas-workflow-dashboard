<?php
namespace extas\components\jsonrpc\transitions;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\jsonrpc\TLoad;
use extas\components\workflows\transitions\Transition;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TransitionLoad
 *
 * @jsonrpc_operation
 * @jsonrpc_name workflow.transition.load
 * @jsonrpc_title Load transitions
 * @jsonrpc_description Load transitions
 * @jsonrpc_request_field data:array
 * @jsonrpc_response_field created_count:int
 * @jsonrpc_response_field got_count:int
 *
 * @method workflowTransitionRepository()
 *
 * @stage run.jsonrpc.transition.load
 * @package extas\components\jsonrpc\transitions
 * @author jeyroik@gmail.com
 */
class TransitionLoad extends OperationDispatcher
{
    use TLoad;

    /**
     * @return ResponseInterface
     */
    public function __invoke(): ResponseInterface
    {
        $request = $this->convertPsrToJsonRpcRequest();
        $transitions = $request->getData();

        return $this->defaultLoad(
            $transitions,
            $this->workflowTransitionRepository(),
            Transition::class,
            $request
        );
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.workflow.transition.load';
    }
}
