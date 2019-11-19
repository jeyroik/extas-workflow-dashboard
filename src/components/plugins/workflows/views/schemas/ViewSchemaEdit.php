<?php
namespace extas\components\plugins\workflows\views\schemas;

use extas\components\dashboards\DashboardList;
use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplate;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
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
         * @var $repo IWorkflowSchemaRepository
         * @var $schema IWorkflowSchema
         * @var $templatesRepo IWorkflowEntityTemplateRepository
         * @var $templates IWorkflowEntityTemplate[]
         */
        $repo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schema = $repo->one([IWorkflowSchema::FIELD__NAME => $args['name'] ?? '']);

        if (!$schema) {
            $response->withHeader('Location', '/');
        } else {
            $editTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/edit']);
            $schema['transitions'] = implode(', ', $schema->getTransitionsNames());

            $templatesRepo = SystemContainer::getItem(IWorkflowEntityTemplateRepository::class);
            $entity = new DashboardList([
                DashboardList::FIELD__SELECTED => $schema->getEntityTemplateName(),
                DashboardList::FIELD__TITLE => 'Шаблон сущности',
                DashboardList::FIELD__NAME => 'entity_template',
                DashboardList::FIELD__ITEMS => $templatesRepo->all([])
            ]);
            $schema['entity_template'] = $entity->render();

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
}
