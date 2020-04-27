<?php
namespace extas\components\plugins\workflows\views\schemas;

use extas\components\dashboards\DashboardView;
use extas\components\dashboards\TDashboardChart;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewSchemaSave
 *
 * @stage view.schemas.save
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewSchemaSave extends Plugin
{
    use TDashboardChart;

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
        $itemsView = '';
        $itemView = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/item']);
        $footer = '';
        $schemaName = $args['name'] ?? '';
        $schemaTitle = $_REQUEST['title'] ?? '';
        $schemaDesc = $_REQUEST['description'] ?? '';
        $schemaTransitions = $_REQUEST['transitions'] ?? '';
        $schemaEntity = $_REQUEST['entity_template'] ?? '';

        foreach ($schemas as $index => $schema) {
            if ($schema->getName() == $schemaName) {
                preg_match_all('/[^,\s]+/', $schemaTransitions, $matches);
                $schema
                    ->setTitle($schemaTitle)
                    ->setDescription($schemaDesc)
                    ->setTransitionsNames($matches[0] ?? [])
                    ->setEntityName($schemaEntity);
                $schemaRepo->update($schema);
            }
            $transitions = '';
            $transitionsSelf = $schema->getTransitions();
            foreach ($transitionsSelf as $transition) {
                $transitions .= $transition->getTitle() . ', ';
            }
            $schema['transitions'] = $transitions;
            $itemsView .= $itemView->render(['schema' => $schema]);
            $footer .= $this->makeChart($schema, $transitionsSelf);
        }

        $list = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/index']);
        $listView = $list->render(['schemas' => $itemsView]);

        $pageView = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageView->render([
            'page' => [
                'title' => 'Схемы',
                'head' => (new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/chart.source']))->render(),
                'content' => $listView,
                'footer' => $footer
            ]
        ]);

        $response->getBody()->write($page);
    }
}
