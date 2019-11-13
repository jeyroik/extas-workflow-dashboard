<?php
namespace extas\components\plugins\workflows\views;

use extas\components\plugins\Plugin;
use extas\components\Replace;
use extas\components\SystemContainer;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\schemas\IWorkflowSchemaRepository;
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
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $schemaRepo IWorkflowSchemaRepository
         * @var $schemas IWorkflowSchema[]
         */
        $schemaRepo = SystemContainer::getItem(IWorkflowSchemaRepository::class);
        $schemas = $schemaRepo->all([]);
        $replace = new Replace();
        $itemsView = '';
        $itemTemplate = file_get_contents(APP__ROOT . '/src/views/schemas/item.php');
        foreach ($schemas as $index => $schema) {
            $transitions = '';
            $transitionsSelf = $schema->getTransitions();
            foreach ($transitionsSelf as $transition) {
                $transitions .= $transition->getTitle() . ', ';
            }
            $schema['transitions'] = $transitions;
            $itemsView .= $replace->apply(['schema' => $schema])->to($itemTemplate);
        }

        $listTemplate = file_get_contents(APP__ROOT . '/src/views/schemas/index.php');
        $listView = $replace->apply(['schemas' => $itemsView])->to($listTemplate);

        $pageTemplate = file_get_contents(APP__ROOT . '/src/views/layouts/main.php');
        $page = $replace->apply([
            'page' => [
                'title' => 'Схемы',
                'head' => '',
                'content' => $listView,
                'footer' => ''
            ]
        ])->to($pageTemplate);

        $response->getBody()->write($page);
    }
}
