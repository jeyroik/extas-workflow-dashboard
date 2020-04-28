<?php
namespace extas\components\dashboards;

use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\transitions\ITransition;

/**
 * Trait TDashboardChart
 *
 * @package extas\components\dashboards
 * @author jeyroik@gmail.com
 */
trait TDashboardChart
{
    /**
     * @param ISchema $schema
     * @param ITransition[] $transitions
     * @return string
     */
    protected function makeChart(ISchema $schema, array $transitions)
    {
        $chartTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/chart']);
        $chartData = [];
        $nodes = [];
        $states = [];
        foreach ($transitions as $transition) {
            $chartData[] = [
                'from' => $transition->getStateFromName(),
                'to' => $transition->getStateToName(),
                'dataLabels' => [
                    'linkFormat' => $transition->getTitle() . '<br>{point.fromNode.name} \u2192 {point.toNode.name}'
                ]
            ];
            $this->addStatesFrom($states, $transition, $nodes);
            $this->addStatesTo($states, $transition, $nodes);
        }

        return $this->renderChart($chartTemplate, $chartData, $schema, $nodes);
    }

    /**
     * @param $chartTemplate
     * @param $chartData
     * @param $schema
     * @param $nodes
     * @return string
     */
    protected function renderChart($chartTemplate, $chartData, $schema, $nodes)
    {
        return $chartTemplate->render([
            'chart' => [
                'name' => $schema->getName(),
                'title' => $schema->getTitle(),
                'subTitle' => $schema->getDescription(),
                'data' => json_encode($chartData),
                'nodes' => json_encode($nodes)
            ]
        ]);
    }

    /**
     * @param array $states
     * @param ITransition $transition
     * @param array $nodes
     */
    protected function addStatesTo(array $states, ITransition $transition, array &$nodes)
    {
        $this->addStates($states, $transition, $nodes, 'To');
    }

    /**
     * @param array $states
     * @param ITransition $transition
     * @param array $nodes
     */
    protected function addStatesFrom(array $states, ITransition $transition, array &$nodes)
    {
        $this->addStates($states, $transition, $nodes, 'From');
    }

    /**
     * @param array $states
     * @param ITransition $transition
     * @param array $nodes
     * @param string $dir
     */
    protected function addStates(array $states, ITransition $transition, array &$nodes, string $dir = 'From')
    {
        $nameMethod = 'getState' . $dir . 'Name';
        $stateMethod = 'getState' . $dir;
        if (!isset($states[$transition->$nameMethod()])) {
            $title = ($state = $transition->$stateMethod()) ? $state->getTitle() : '';
            $states[$transition->$nameMethod()] = true;
            $nodes[] = [
                'id' => $transition->getStateFromName(),
                'dataLabels' => [
                    'format' => $title
                ]
            ];
        }
    }
}
