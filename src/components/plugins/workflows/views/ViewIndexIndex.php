<?php
namespace extas\components\plugins\workflows\views;

use extas\components\dashboards\DashboardView;
use extas\components\dashboards\TDashboardChart;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewIndexIndex
 *
 * @stage view.index.index
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewIndexIndex extends Plugin
{
    use TDashboardChart;

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
        foreach ($schemas as $index => $schema) {
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
}
