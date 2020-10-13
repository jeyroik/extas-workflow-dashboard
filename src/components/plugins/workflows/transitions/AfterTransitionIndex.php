<?php
namespace extas\components\plugins\workflows\transitions;

use extas\components\http\THasJsonRpcRequest;
use extas\components\http\THasJsonRpcResponse;
use extas\components\plugins\Plugin;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityContext;
use extas\components\workflows\transits\TransitResult;
use extas\interfaces\jsonrpc\IResponse;
use extas\interfaces\operations\IJsonRpcOperation;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\stages\IStageAfterJsonRpcOperation;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AfterTransitionIndex
 *
 * @method IRepository workflowTransitions()
 *
 * @package extas\components\plugins\workflows\transitions
 * @author jeyroik <jeyroik@gmail.com>
 */
class AfterTransitionIndex extends Plugin implements IStageAfterJsonRpcOperation
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
        $request = $this->getJsonRpcRequest();
        $context = new EntityContext($request->getParams()['context']);
        $entity = new Entity($request->getParams()['entity']);
        $responseData = json_decode($response->getBody(), true);
        $transitionsData = $responseData[IResponse::FIELD__RESULT]['items'];
        $transitionsNames = array_column($transitionsData, ITransition::FIELD__NAME);

        /**
         * @var ITransition[] $transitions
         */
        $transitions = $this->workflowTransitions()->all([ITransition::FIELD__NAME => $transitionsNames]);
        $result = new TransitResult();

        $valid = [];
        foreach ($transitions as $transition) {
            $conditions = $transition->getConditions();
            if (empty($conditions)) {
                $valid[] = $transition->__toArray();
                continue;
            }

            foreach ($conditions as $condition) {
                if ($condition->dispatch($context, $result, $entity)) {
                    $valid[] = $transition->__toArray();
                }
            }
        }

        return $this->successResponse(
            $this->getJsonRpcRequest()->getId(),
            [
                'items' => $valid,
                'total' => count($valid)
            ]
        );
    }
}
