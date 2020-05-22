<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\components\plugins\workflows\views\TTransitionView;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionCreate
 *
 * @method workflowTransitionRepository()
 *
 * @stage view.transitions.create
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionCreate extends Plugin
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
        $transitions = $this->workflowTransitionRepository()->all([]);
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/item']);
        $editTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/edit']);

        array_unshift($transitions, new Transition([
            Transition::FIELD__TITLE => '',
            Transition::FIELD__DESCRIPTION => '',
            Transition::FIELD__NAME => '__created__'
        ]));

        foreach ($transitions as $index => $transition) {
            if ($transition->getName() == '__created__') {
                $this->renderStates($transition);
                $itemsView .= $editTemplate->render(['transition' => $transition]);
            } else {
                $itemsView .= $itemTemplate->render(['transition' => $transition]);
            }
        }
        $this->renderPage($itemsView, $response, 'transitions', 'Переходы');
    }
}
