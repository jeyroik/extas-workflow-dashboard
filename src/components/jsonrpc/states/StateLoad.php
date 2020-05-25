<?php
namespace extas\components\jsonrpc\states;

use extas\components\jsonrpc\operations\OperationDispatcher;
use extas\components\jsonrpc\TLoad;
use extas\components\workflows\states\State;
use Psr\Http\Message\ResponseInterface;

/**
 * Class StateLoad
 *
 * @jsonrpc_operation
 * @jsonrpc_name workflow.state.load
 * @jsonrpc_title Load states
 * @jsonrpc_description Load states
 * @jsonrpc_request_field data:array
 * @jsonrpc_response_field created_count:int
 * @jsonrpc_response_field got_count:int
 *
 * @method workflowStateRepository()
 *
 * @stage run.jsonrpc.state.load
 * @package extas\components\jsonrpc\states
 * @author jeyroik@gmail.com
 */
class StateLoad extends OperationDispatcher
{
    use TLoad;

    /**
     * @return ResponseInterface
     */
    public function __invoke(): ResponseInterface
    {
        $request = $this->convertPsrToJsonRpcRequest();
        $states = $request->getData();

        return $this->defaultLoad(
            $states,
            $this->workflowStateRepository(),
            State::class,
            $request
        );
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.workflow.state.load';
    }
}
