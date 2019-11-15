<?php
namespace extas\components\plugins\workflows\jsonrpc\schemas;

use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcher;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplate;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherTemplateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRpcSchemaTransitionAdd
 *
 * @stage run.jsonrpc.schema.transition.add
 * @package extas\components\plugins\workflows\jsonrpc\schemas
 * @author jeyroik@gmail.com
 */
class JsonRpcSchemaTransitionAdd extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $jRpcData
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array &$jRpcData)
    {
        $transitionName = $jRpcData['transition_name'] ?? '';
        $schemaName = $jRpcData['schema_name'] ?? '';
        $dispatchersData = $jRpcData['dispatchers'];

        /**
         * @var $transitRepo IWorkflowTransitionRepository
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $dispatcherRepo ITransitionDispatcherRepository
         * @var $templateRepo ITransitionDispatcherTemplateRepository
         * @var $schema IWorkflowSchema
         */
        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transition = $transitRepo->one([IWorkflowTransition::FIELD__NAME => $transitionName]);
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schema = $schemaRepo->one([IWorkflowSchema::FIELD__NAME => $schemaName]);

        if (!$schema->hasTransition($transitionName)) {
            $schema->addTransition($transition);
            $schemaRepo->update($schema);
        }
        $dispatcherRepo = SystemContainer::getItem(ITransitionDispatcherRepository::class);
        $unknownTemplates = $this->getUnknownTemplates($dispatchersData);

        foreach ($dispatchersData as $dispatchersDatum) {
            $dispatchersDatum[ITransitionDispatcher::FIELD__SCHEMA_NAME] = $schemaName;
            $dispatchersDatum[ITransitionDispatcher::FIELD__TRANSITION_NAME] = $transitionName;
            $dispatcher = new TransitionDispatcher($dispatchersDatum);
            if (isset($unknownTemplates[$dispatcher->getTemplateName()])) {
                continue;
            }
            $dispatcherRepo->create($dispatcher);
        }

        $response = $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
        $response
            ->getBody()->write(json_encode([
                'id' => $jRpcData['id'] ?? '',
                'result' => [
                    'name' => $transitionName
                ]
            ]));
    }

    /**
     * @param $dispatchers
     *
     * @return array
     */
    protected function getUnknownTemplates($dispatchers)
    {
        $templatesNames = array_column($dispatchers, ITransitionDispatcher::FIELD__TEMPLATE);

        /**
         * @var $repo ITransitionDispatcherTemplateRepository
         * @var $templates ITransitionDispatcherTemplate[]
         */
        $repo = SystemContainer::getItem(ITransitionDispatcherTemplateRepository::class);
        $templates = $repo->all([ITransitionDispatcherTemplate::FIELD__NAME => $templatesNames]);
        $existedNames = [];
        foreach ($templates as $template) {
            $existedNames[] = $template->getName();
        }

        $unknown = array_diff($templatesNames, $existedNames);

        return array_flip($unknown);
    }
}
