<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardList;
use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionCreate
 *
 * @stage view.transitions.create
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionCreate extends Plugin
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

        array_unshift($transitions, new WorkflowTransition());

        foreach ($transitions as $index => $transition) {
            if (!$transition->getName()) {
                $this->renderStates($transition);
                $itemsView .= $editTemplate->render(['transition' => $transition]);
            } else {
                $itemsView .= $itemTemplate->render(['transition' => $transition]);
            }
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

    /**
     * @param IWorkflowTransition $transition
     */
    protected function renderStates(IWorkflowTransition &$transition)
    {
        /**
         * @var $statesRepo IWorkflowStateRepository
         * @var $states IWorkflowState[]
         */
        $statesRepo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $states = $statesRepo->all([]);

        $list = new DashboardList([
            DashboardList::FIELD__ITEMS => $states,
            DashboardList::FIELD__NAME => 'state_from',
            DashboardList::FIELD__TITLE => 'Из состояния',
            DashboardList::FIELD__SELECTED => $transition->getStateFromName()
        ]);
        $transition['state_from'] = $list->render();

        $list = new DashboardList([
            DashboardList::FIELD__ITEMS => $states,
            DashboardList::FIELD__NAME => 'state_to',
            DashboardList::FIELD__TITLE => 'В состояние',
            DashboardList::FIELD__SELECTED => $transition->getStateToName()
        ]);
        $transition['state_to'] = $list->render();
    }
}
