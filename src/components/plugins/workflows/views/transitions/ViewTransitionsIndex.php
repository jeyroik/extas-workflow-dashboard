<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\ITransition;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionsIndex
 *
 * @method IRepository workflowTransitions()
 *
 * @stage view.transitions.index
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionsIndex extends Plugin
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
         * @var $transitions ITransition[]
         */
        $transitions = $this->workflowTransitions()->all([]);
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/item']);

        foreach ($transitions as $index => $transition) {
            $itemsView .= $itemTemplate->render(['transition' => $transition]);
        }

        $this->renderPage($itemsView, $response, 'transitions', 'Переходы');
    }
}
