<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionEdit
 *
 * @stage view.transitions.edit
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionEdit extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $repo IWorkflowTransitionRepository
         * @var $transitions IWorkflowTransition[]
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transitions = $repo->all([]);
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/item']);
        $editTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/edit']);

        $transitionName = $args['name'] ?? '';

        foreach ($transitions as $index => $transition) {
            $itemsView .= $transition->getName() == $transitionName
                ? $editTemplate->render(['transition' => $transition])
                : $itemTemplate->render(['transition' => $transition]);
        }

        $listTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/index']);
        $listView = $listTemplate->render(['transitions' => $itemsView]);

        $pageTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageTemplate->render([
            'page' => [
                'title' => 'Переходы',
                'head' => '',
                'content' => $listView,
                'footer' => ''
            ]
        ]);

        $response->getBody()->write($page);
    }
}
