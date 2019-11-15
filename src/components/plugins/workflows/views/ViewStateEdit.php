<?php
namespace extas\components\plugins\workflows\views;

use extas\components\plugins\Plugin;
use extas\components\Replace;
use extas\components\SystemContainer;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewStateEdit
 *
 * @stage view.states.edit
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewStateEdit extends Plugin
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $stateRepo IWorkflowStateRepository
         * @var $states IWorkflowState[]
         */
        $stateRepo = SystemContainer::getItem(IWorkflowStateRepository::class);
        $states = $stateRepo->all([]);
        $replace = new Replace();
        $itemsView = '';
        $itemTemplate = file_get_contents(APP__ROOT . '/src/views/states/item.php');
        $editTemplate = file_get_contents(APP__ROOT . '/src/views/states/edit.php');

        $stateName = $args['name'] ?? '';

        foreach ($states as $index => $state) {
            $itemsView .= $state->getName() == $stateName
                ? $replace->apply(['state' => $state])->to($editTemplate)
                : $replace->apply(['state' => $state])->to($itemTemplate);
        }

        $listTemplate = file_get_contents(APP__ROOT . '/src/views/states/index.php');
        $listView = $replace->apply(['states' => $itemsView])->to($listTemplate);

        $pageTemplate = file_get_contents(APP__ROOT . '/src/views/layouts/main.php');
        $page = $replace->apply([
            'page' => [
                'title' => 'Состояния',
                'head' => '',
                'content' => $listView,
                'footer' => ''
            ]
        ])->to($pageTemplate);

        $response->getBody()->write($page);
    }
}
