<?php
namespace extas\components\plugins\workflows\views\states;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\components\workflows\states\State;
use extas\interfaces\workflows\states\IState;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewStateCreate
 *
 * @method workflowStateRepository()
 *
 * @stage view.states.create
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewStateCreate extends Plugin
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

        array_unshift($states, new State([
            State::FIELD__TITLE => '',
            State::FIELD__DESCRIPTION => '',
            State::FIELD__NAME => '__created__'
        ]));

        foreach ($states as $index => $state) {
            $itemsView .= $state->getName() == '__created__'
                ? $editTemplate->render(['state' => $state])
                : $itemTemplate->render(['state' => $state]);
        }

        $this->renderPage($itemsView, $response);
    }
}
