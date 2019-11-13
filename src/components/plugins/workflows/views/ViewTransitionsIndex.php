<?php
namespace extas\components\plugins\workflows\views;

use extas\components\plugins\Plugin;
use extas\components\Replace;
use extas\components\SystemContainer;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionsIndex
 *
 * @stage view.transitions.index
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionsIndex extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $repo IWorkflowTransitionRepository
         * @var $transitions IWorkflowTransition[]
         */
        $repo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transitions = $repo->all([]);
        $replace = new Replace();
        $itemsView = '';
        $itemTemplate = file_get_contents(APP__ROOT . '/src/views/transitions/item.php');

        foreach ($transitions as $index => $transition) {
            $itemsView .= $replace->apply(['transition' => $transition])->to($itemTemplate);
        }

        $listTemplate = file_get_contents(APP__ROOT . '/src/views/transitions/index.php');
        $listView = $replace->apply(['transitions' => $itemsView])->to($listTemplate);

        $pageTemplate = file_get_contents(APP__ROOT . '/src/views/layouts/main.php');
        $page = $replace->apply([
            'page' => [
                'title' => 'Переходы',
                'head' => '',
                'content' => $listView,
                'footer' => ''
            ]
        ])->to($pageTemplate);

        $response->getBody()->write($page);
    }
}
