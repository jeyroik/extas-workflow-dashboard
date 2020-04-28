<?php
namespace extas\components\plugins\workflows\views;

use extas\components\dashboards\DashboardView;
use extas\components\dashboards\TDashboardChart;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewIndexIndex
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
         * @var $schemaRepo ISchemaRepository
         * @var $schemas ISchema[]
         */
        $schemaRepo = SystemContainer::getItem(ISchemaRepository::class);
        $schemas = $schemaRepo->all([]);
        $itemView = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/item']);
        $itemsView = '';
        $footer = '';

        foreach ($schemas as $index => $schema) {
            $this->buildTransitions($schema, $itemView, $itemsView, $footer);
        }
        $this->renderPage($itemsView, $footer, $response);
    }
}
