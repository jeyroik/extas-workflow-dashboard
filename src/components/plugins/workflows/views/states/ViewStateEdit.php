<?php
namespace extas\components\plugins\workflows\views\states;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewStateEdit
 *
 * @stage view.states.edit
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewStateEdit extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $stateRepo IWorkflowStateRepository
         * @var $states IWorkflowState[]
         */
        $stateRepo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $states = $stateRepo->all([]);
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'states/item']);
        $editTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'states/edit']);

        $stateName = $args['name'] ?? '';

        foreach ($states as $index => $state) {
            if ($state->getName() == $stateName) {
                $itemsView = $editTemplate->render(['state' => $state]) . $itemsView;
            } else {
                $itemsView .= $itemTemplate->render(['state' => $state]);
            }
        }

        $listTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'states/index']);
        $listView = $listTemplate->render(['states' => $itemsView]);

        $pageTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageTemplate->render([
            'page' => [
                'title' => 'Состояния',
                'head' => '',
                'content' => $listView,
                'footer' => ''
            ]
        ]);

        $response->getBody()->write($page);
    }
}
