<?php
namespace extas\components\plugins\workflows\jsonrpc\before;

use extas\components\jsonrpc\JsonRpcErrors;
use extas\components\plugins\workflows\jsonrpc\JsonRpcValidationPlugin;
use extas\components\SystemContainer;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\interfaces\IHasName;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BeforeSchemaDelete
 *
 * @stage before.run.jsonrpc.schema.delete
 * @package extas\components\plugins\workflows\jsonrpc\before
 * @author jeyroik@gmail.com
 */
class BeforeSchemaDelete extends JsonRpcValidationPlugin
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
            $item = new WorkflowSchema($jRpcData);
            /**
             * @var $repo IRepository
             */
            $repo = SystemContainer::getItem(IWorkflowStateRepository::class);
            if (!$repo->one([IHasName::FIELD__NAME => $item->getName()])) {
                $this->setResponseError(
                    $response,
                    $jRpcData,
                    JsonRpcErrors::ERROR__UNKNOWN_ENTITY,
                    [WorkflowSchema::FIELD__NAME => $item->getName()]
                );
            } else {
                $this->checkTransitionDispatchers($item);
            }
        }
    }

    /**
     * @param IWorkflowSchema $item
     */
    protected function checkTransitionDispatchers(IWorkflowSchema $item)
    {
        /**
         * @var $repo ITransitionDispatcherRepository
         * @var $dispatchers ITransitionDispatcher[]
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $dispatchers = $repo->all([
            ITransitionDispatcher::FIELD__SCHEMA_NAME => $item->getName()
        ]);
        if (count($dispatchers)) {
            $repo->delete([ITransitionDispatcher::FIELD__SCHEMA_NAME => $item->getName()]);
        }
    }
}
