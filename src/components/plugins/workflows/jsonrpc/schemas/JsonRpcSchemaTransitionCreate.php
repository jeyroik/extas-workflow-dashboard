<?php
namespace extas\components\plugins\workflows\jsonrpc\schemas;

use extas\components\jsonrpc\JsonRpcData;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\extensions\jsonrpc\IDataExtensionSchemaTransitionCreate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcSchemaTransitionCreate
 *
 * @stage run.jsonrpc.schema.transition.create
 * @package extas\components\plugins\workflows\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class JsonRpcSchemaTransitionCreate extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        /**
         * @var $jsonData IDataExtensionSchemaTransitionCreate
         * @var $transitRepo IWorkflowTransitionRepository
         * @var $dispatcherRepo ITransitionDispatcherRepository
         */
        $jsonData = new JsonRpcData($jRpcData);
        $transition = $jsonData->getTransition();
        $dispatcher = $jsonData->getTransitionDispatcher();

        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $existed = $transitRepo->one([IWorkflowTransition::FIELD__NAME => $transition->getName()]);
        !$existed && $transitRepo->create($transition);

        $dispatcherRepo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $existed = $dispatcherRepo->one([ITransitionDispatcher::FIELD__NAME => $dispatcher->getName()]);

        if (!$existed) {
            $dispatcherRepo->create($existed);
        }

        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);

        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => [
                    IDataExtensionSchemaTransitionCreate::FIELD__TRANSITION => $transition->__toArray(),
                    IDataExtensionSchemaTransitionCreate::FIELD__DISPATCHER => $dispatcher->__toArray()
                ]
            ]));
    }
}
