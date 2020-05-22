<?php
namespace extas\components\plugins\workflows\views\states;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\interfaces\workflows\states\IState;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewStateEdit
 *
 * @method workflowStateRepository()
 *
 * @stage view.states.edit
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewStateEdit extends Plugin
{
    use TItemsView;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $states IState[]
         */
        $stateRepo = $this->workflowStateRepository();
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
        $this->renderPage($itemsView, $response);
    }
}
