<?php
namespace extas\components\plugins\workflows\views;

use extas\components\dashboards\DashboardView;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait TStateView
 *
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
trait TItemsView
{
    /**
     * @param string $itemsView
     * @param ResponseInterface $response
     * @param string $viewPrefix
     * @param string $title
     */
    protected function renderPage(
        string $itemsView,
        ResponseInterface &$response,
        string $viewPrefix = 'states',
        string $title = 'Состояния'
    )
    {
        $listTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => $viewPrefix . '/index']);
        $listView = $listTemplate->render([$viewPrefix => $itemsView]);

        $pageTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageTemplate->render([
            'page' => [
                'title' => $title,
                'head' => '',
                'content' => $listView,
                'footer' => ''
            ]
        ]);

        $response->getBody()->write($page);
    }
}
