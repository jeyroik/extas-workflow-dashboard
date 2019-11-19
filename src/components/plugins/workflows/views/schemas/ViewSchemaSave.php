<?php
namespace extas\components\plugins\workflows\views\schemas;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewSchemaSave
 *
 * @stage view.schemas.save
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewSchemaSave extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schemas IWorkflowSchema[]
         */
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schemas = $schemaRepo->all([]);
        $itemsView = '';
        $itemView = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/item']);
        $footer = '';
        $schemaName = $args['name'] ?? '';
        $schemaTitle = $_REQUEST['title'] ?? '';
        $schemaDesc = $_REQUEST['description'] ?? '';
        $schemaTransitions = $_REQUEST['transitions'] ?? '';
        $schemaEntity = $_REQUEST['entity_template'] ?? '';

        foreach ($schemas as $index => $schema) {
            if ($schema->getName() == $schemaName) {
                preg_match_all('/[^,\s]+/', $schemaTransitions, $matches);
                $schema
                    ->setTitle($schemaTitle)
                    ->setDescription($schemaDesc)
                    ->setTransitions($matches[0] ?? [])
                    ->setEntityTemplateName($schemaEntity);
                $schemaRepo->update($schema);
            }
            $transitions = '';
            $transitionsSelf = $schema->getTransitions();
            foreach ($transitionsSelf as $transition) {
                $transitions .= $transition->getTitle() . ', ';
            }
            $schema['transitions'] = $transitions;
            $itemsView .= $itemView->render(['schema' => $schema]);
            $footer .= $this->makeChart($schema, $transitionsSelf);
        }

        $list = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/index']);
        $listView = $list->render(['schemas' => $itemsView]);

        $pageView = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageView->render([
            'page' => [
                'title' => 'Схемы',
                'head' => (new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/chart.source']))->render(),
                'content' => $listView,
                'footer' => $footer
            ]
        ]);

        $response->getBody()->write($page);
    }

    /**
     * @param IWorkflowSchema $schema
     * @param IWorkflowTransition[] $transitions
     * @return string
     */
    protected function makeChart(IWorkflowSchema $schema, array $transitions)
    {
        $chartTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/chart']);
        $chartData = [];
        $nodes = [];
        $states = [];
        foreach ($transitions as $transition) {
            $chartData[] = [
                'from' => $transition->getStateFromName(),
                'to' => $transition->getStateToName(),
                'dataLabels' => [
                    // ex. Not actual (todo -> not_actual)
                    'linkFormat' => $transition->getTitle() . '<br>{point.fromNode.name} \u2192 {point.toNode.name}'
                ]
            ];
            if (!isset($states[$transition->getStateFromName()])) {
                $states[$transition->getStateFromName()] = true;
                $nodes[] = [
                    'id' => $transition->getStateFromName(),
                    'dataLabels' => [
                        'format' => $transition->getStateFrom()->getTitle()
                    ]
                ];
            }
            if (!isset($states[$transition->getStateToName()])) {
                $states[$transition->getStateToName()] = true;
                $nodes[] = [
                    'id' => $transition->getStateToName(),
                    'dataLabels' => [
                        'format' => $transition->getStateTo()->getTitle()
                    ]
                ];
            }
        }

        return $chartTemplate->render([
            'chart' => [
                'name' => $schema->getName(),
                'title' => $schema->getTitle(),
                'subTitle' => $schema->getDescription(),
                'data' => json_encode($chartData),
                'nodes' => json_encode($nodes)
            ]
        ]);
    }
}