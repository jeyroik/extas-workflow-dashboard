<?php
namespace extas\components\plugins\workflows\jsonrpc\before;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\IHasName;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeTransitionDelete
 *
 * @stage before.run.jsonrpc.transition.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDelete extends JsonRpcValidationPlugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData = [])
    {
        if (!$this->isThereError($jRpcData)) {
            $response = $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
            $item = new WorkflowTransition($jRpcData);
            /**
             * @var $repo IRepository
             */
            $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
            if (!$repo->one([IHasName::FIELD__NAME => $item->getName()])) {
                $this->setResponseError(
                    $response,
                    $jRpcData,
                    JsonRpcErrors::ERROR__UNKNOWN_ENTITY,
                    [WorkflowTransition::FIELD__NAME => $item->getName()]
                );
            } else {
                $this->checkSchemas($response, $jRpcData, $item);
                $this->checkTransitionDispatchers($response, $jRpcData, $item);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param IWorkflowTransition $item
     */
    protected function checkSchemas(ResponseInterface &$response, array &$jRpcData, IWorkflowTransition $item)
    {
        /**
         * @var $repo IWorkflowSchemaRepository
         * @var $schemas IWorkflowSchema[]
         */
        $repo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schemas = $repo->all([
            IWorkflowSchema::FIELD__TRANSITIONS => $item->getName()
        ]);
        if (count($schemas)) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__THERE_ARE_SCHEMAS_WITH_TRANSITION,
                $this->prepare($schemas)
            );
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array $jRpcData
     * @param IWorkflowTransition $item
     */
    protected function checkTransitionDispatchers(
        ResponseInterface &$response,
        array &$jRpcData,
        IWorkflowTransition $item
    )
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__TRANSITION_NAME => $item->getName()
        ]);
        if (count($dispatchers)) {
            $this->setResponseError(
                $response,
                $jRpcData,
                JsonRpcErrors::ERROR__THERE_ARE_DISPATCHERS_FOR_TRANSITION,
                $this->prepare($dispatchers)
            );
        }
    }
}
