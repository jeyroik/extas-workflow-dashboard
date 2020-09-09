<?php
namespace extas\components\jsonrpc\states;

use extas\components\api\jsonrpc\operations\OperationRunner;
use extas\components\jsonrpc\TLoad;
use extas\components\workflows\states\State;
use extas\interfaces\http\IHasHttpIO;
use extas\interfaces\repositories\IRepository;

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
 * @method IRepository workflowStates()
 *
 * @stage run.jsonrpc.state.load
 * @package extas\components\jsonrpc\states
 * @author jeyroik@gmail.com
 */
class StateLoad extends OperationRunner implements IHasHttpIO
{
    use TLoad;

    /**
     * @return array
     */
    public function run(): array
    {
        $states = $this->getJsonRpcRequest()->getData();

        return $this->defaultLoad(
            $states,
            $this->workflowStates(),
            State::class
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
