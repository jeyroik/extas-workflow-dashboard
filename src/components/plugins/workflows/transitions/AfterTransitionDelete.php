<?php
namespace extas\components\plugins\workflows\transitions;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\plugins\Plugin;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageAfterJsonRpcOperation;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeTransitionDelete
 *
 * @method IRepository workflowTransitionsDispatchers()
 *
 * @stage extas.workflow_transitions.delete.before
 * @package extas\components\plugins\workflows\transitions
 * @author jeyroik@gmail.com
 */
class AfterTransitionDelete extends Plugin implements IStageAfterJsonRpcOperation
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
        if ($operation->getName() === 'workflow.transition.delete') {
            $request = $this->getJsonRpcRequest();
            $data = $request->getData([]);
            $name = $data[ITransition::FIELD__NAME] ?? '';

            $this->workflowTransitionsDispatchers()->delete([
                ITransitionDispatcher::FIELD__TRANSITION_NAME => $name
            ]);
        }

        return $response;
    }
}
