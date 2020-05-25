<?php
namespace extas\components\plugins\workflows\views\states;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\interfaces\workflows\states\IState;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewStatesIndex
 *
 * @method workflowStateRepository()
 *
 * @stage view.states.index
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewStatesIndex extends Plugin
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
        $states = $this->workflowStateRepository()->all([]);
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'states/item']);

        foreach ($states as $index => $state) {
            $itemsView .= $itemTemplate->render(['state' => $state]);
        }
        $this->renderPage($itemsView, $response);
    }
}
