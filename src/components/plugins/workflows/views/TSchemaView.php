<?php
namespace extas\components\plugins\workflows\views;

use extas\components\dashboards\DashboardView;
use extas\interfaces\workflows\schemas\ISchema;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait TBuildTransitions
 *
 * @method makeChart($schema, $transitionsSelf)
 *
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
trait TSchemaView
{
    /**
     * @param ISchema $schema
     * @param $itemView
     * @param string $itemsView
     * @param string $footer
     */
    protected function buildTransitions(ISchema $schema, $itemView, string &$itemsView, string &$footer)
    {
        $transitions = '';
        $transitionsSelf = $schema->getTransitions();
        foreach ($transitionsSelf as $transition) {
            $transitions .= $transition->getTitle() . ', ';
        }
        $schema['transitions'] = $transitions;
        $itemsView .= $itemView->render(['schema' => $schema]);
        $footer .= $this->makeChart($schema, $transitionsSelf);
    }

    /**
     * @param string $itemsView
     * @param string $footer
     * @param ResponseInterface $response
     */
    protected function renderPage(string $itemsView, string $footer, ResponseInterface &$response)
    {
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
