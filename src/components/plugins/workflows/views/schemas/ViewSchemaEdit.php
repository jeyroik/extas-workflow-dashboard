<?php
namespace extas\components\plugins\workflows\views\schemas;

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
            $templates = $templatesRepo->all([]);
            $schema['entity_templates'] = $this->drawTemplatesList($templates, $schema);

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

    /**
     * @param IWorkflowEntityTemplate[] $templates
     * @param IWorkflowSchema $schema
     * @return string
     */
    protected function drawTemplatesList(array $templates, IWorkflowSchema $schema): string
    {
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/select.item']);
        $items = '';
        $currentTemplate = $schema->getEntityTemplateName();
        foreach ($templates as $template) {
            $template['selected'] = $template->getName() == $currentTemplate
                ? 'selected'
                : '';
            $items .= $itemTemplate->render(['item' => $template]);
        }

        $listTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/select.list']);
        return $listTemplate->render([
            'list' => [
                'title' => 'Шаблон сущности',
                'name' => 'entity_template'
            ],
            'items' => $items
        ]);
    }
}
