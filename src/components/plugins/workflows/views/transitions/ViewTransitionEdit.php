<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardList;
use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\states\IStateRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;
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
         * @var $repo ITransitionRepository
         * @var $transitions ITransition[]
         */
        $repo = SystemContainer::getItem(ITransitionRepository::class);
        $transitions = $repo->all([]);
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/item']);
        $editTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/edit']);

        $transitionName = $args['name'] ?? '';

        foreach ($transitions as $index => $transition) {
            if ($transition->getName() == $transitionName) {
                $this->renderStates($transition);
                $itemsView = $editTemplate->render(['transition' => $transition]) . $itemsView;
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
     * @param ITransition $transition
     */
    protected function renderStates(ITransition &$transition)
    {
        /**
         * @var $statesRepo IStateRepository
         * @var $states IState[]
         */
        $statesRepo = SystemContainer::getItem(IStateRepository::class);
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
