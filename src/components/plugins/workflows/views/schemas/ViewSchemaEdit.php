<?php
namespace extas\components\plugins\workflows\views\schemas;

use extas\components\dashboards\DashboardList;
use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\interfaces\workflows\entities\IEntitySample;
use extas\interfaces\workflows\schemas\ISchema;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewSchemaEdit
 *
 * @method workflowSchemaRepository()
 * @method workflowEntitySampleRepository()
 *
 * @stage view.schemas.edit
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewSchemaEdit extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $schema ISchema
         * @var $templates IEntitySample[]
         */
        $schema = $this->workflowSchemaRepository()->one([ISchema::FIELD__NAME => $args['name'] ?? '']);

        if (!$schema) {
            $response = $response->withHeader('Location', '/');
        } else {
            $editTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/edit']);
            $schema['transitions'] = implode(', ', $schema->getTransitionsNames());

            $entity = new DashboardList([
                DashboardList::FIELD__SELECTED => $schema->getEntityName(),
                DashboardList::FIELD__TITLE => 'Сущность',
                DashboardList::FIELD__NAME => 'entity_name',
                DashboardList::FIELD__ITEMS => $this->workflowEntitySampleRepository()->all([])
            ]);
            $schema['entity_name'] = $entity->render();

            $this->renderPage($schema, $editTemplate, $response);
        }
    }

    /**
     * @param $schema
     * @param $editTemplate
     * @param ResponseInterface $response
     */
    protected function renderPage($schema, $editTemplate, ResponseInterface &$response)
    {
        $itemView = $editTemplate->render(['schema' => $schema]);
        $pageTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageTemplate->render([
            'page' => [
                'title' => 'Схемы - Редактирование',
                'head' => '',
                'content' => $itemView,
                'footer' => ''
            ]
        ]);

        $response->getBody()->write($page);
    }
}
