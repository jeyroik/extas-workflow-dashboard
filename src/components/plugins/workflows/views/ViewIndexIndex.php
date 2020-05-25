<?php
namespace extas\components\plugins\workflows\views;

use extas\components\dashboards\DashboardView;
use extas\components\dashboards\TDashboardChart;
use extas\components\plugins\Plugin;
use extas\interfaces\workflows\schemas\ISchema;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewIndexIndex
 *
 * @method workflowSchemaRepository()
 *
 * @stage view.index.index
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewIndexIndex extends Plugin
{
    use TDashboardChart;
    use TSchemaView;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $schemas ISchema[]
         */
        $schemas = $this->workflowSchemaRepository()->all([]);
        $itemView = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/item']);
        $itemsView = '';
        $footer = '';

        foreach ($schemas as $index => $schema) {
            $this->buildTransitions($schema, $itemView, $itemsView, $footer);
        }
        $this->renderPage($itemsView, $footer, $response);
    }
}
