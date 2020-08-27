<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\components\plugins\workflows\views\TTransitionView;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionEdit
 *
 * @method IRepository workflowTransitions()
 *
 * @stage view.transitions.edit
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionEdit extends Plugin
{
    use TItemsView;
    use TTransitionView;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $transitions ITransition[]
         */
        $transitions = $this->workflowTransitions()->all([]);
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
        $this->renderPage($itemsView, $response, 'transitions', 'Переходы');
    }
}
