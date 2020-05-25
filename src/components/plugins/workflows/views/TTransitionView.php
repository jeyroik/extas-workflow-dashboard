<?php
namespace extas\components\plugins\workflows\views;

use extas\components\dashboards\DashboardList;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Trait TTransitionView
 *
 * @method workflowStateRepository()
 *
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
trait TTransitionView
{
    /**
     * @param ITransition $transition
     */
    protected function renderStates(ITransition &$transition)
    {
        /**
         * @var $states IState[]
         */
        $states = $this->workflowStateRepository()->all([]);

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
