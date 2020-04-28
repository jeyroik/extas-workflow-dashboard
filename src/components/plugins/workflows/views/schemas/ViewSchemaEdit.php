<?php
namespace extas\components\plugins\workflows\views\schemas;

use extas\components\dashboards\DashboardList;
use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\entities\IEntitySample;
use extas\interfaces\workflows\entities\IEntitySampleRepository;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewSchemaEdit
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
         * @var $repo ISchemaRepository
         * @var $schema ISchema
         * @var $templatesRepo IEntitySampleRepository
         * @var $templates IEntitySample[]
         */
        $repo = SystemContainer::getItem(ISchemaRepository::class);
        $schema = $repo->one([ISchema::FIELD__NAME => $args['name'] ?? '']);

        if (!$schema) {
            $response = $response->withHeader('Location', '/');
        } else {
            $editTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/edit']);
            $schema['transitions'] = implode(', ', $schema->getTransitionsNames());

            $templatesRepo = SystemContainer::getItem(IEntitySampleRepository::class);
            $entity = new DashboardList([
                DashboardList::FIELD__SELECTED => $schema->getEntityName(),
                DashboardList::FIELD__TITLE => 'Сущность',
                DashboardList::FIELD__NAME => 'entity_name',
                DashboardList::FIELD__ITEMS => $templatesRepo->all([])
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
