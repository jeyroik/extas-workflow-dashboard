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
                    // ex. Not actual (todo -> not_actual)
                    'linkFormat' => $transition->getTitle() . '<br>{point.fromNode.name} \u2192 {point.toNode.name}'
                ]
            ];
            if (!isset($states[$transition->getStateFromName()])) {
                $title = ($state = $transition->getStateFrom()) ? $state->getTitle() : '';
                $states[$transition->getStateFromName()] = true;
                $nodes[] = [
                    'id' => $transition->getStateFromName(),
                    'dataLabels' => [
                        'format' => $title
                    ]
                ];
            }
            if (!isset($states[$transition->getStateToName()])) {
                $title = ($state = $transition->getStateTo()) ? $state->getTitle() : '';
                $states[$transition->getStateToName()] = true;
                $nodes[] = [
                    'id' => $transition->getStateToName(),
                    'dataLabels' => [
                        'format' => $title
                    ]
                ];
            }
        }

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
}
