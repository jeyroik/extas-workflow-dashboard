<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionSave
 *
 * @stage view.transitions.save
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionSave extends Plugin
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
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/item']);

        $transitionName = $args['name'] ?? '';
        $transitionTitle = $_REQUEST['title'] ?? '';
        $transitionDesc = $_REQUEST['description'] ?? '';
        $transitionStateFrom = $_REQUEST['state_from'] ?? '';
        $transitionStateTo = $_REQUEST['state_to'] ?? '';

        $updated = false;

        foreach ($transitions as $index => $transition) {
            if ($transition->getName() == $transitionName) {
                $transition
                    ->setTitle($transitionTitle)
                    ->setDescription($transitionDesc)
                    ->setStateFrom($transitionStateFrom)
                    ->setStateTo($transitionStateTo);
                $repo->update($transition);
                $updated = true;
            }
            $itemsView .= $itemTemplate->render(['transition' => $transition]);
        }

        if (!$updated) {
            $this->createTransition(
                $transitionTitle, $transitionDesc, $transitionStateFrom,
                $transitionStateTo, $itemTemplate, $repo, $itemsView
            );
        }

        $listTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/index']);
        $listView = $listTemplate->render(['transitions' => $itemsView]);

        $pageTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageTemplate->render([
            'page' => [
                'title' => 'Переходы',
                'head' => '',
                'content' => $listView,
                'footer' => ''
            ]
        ]);

        $response->getBody()->write($page);
    }

    /**
     * @param string $title
     * @param string $description
     * @param string $stateFrom
     * @param string $stateTo
     * @param DashboardView $template
     * @param IWorkflowTransitionRepository $repo
     * @param string $view
     */
    protected function createTransition($title, $description, $stateFrom, $stateTo, $template, $repo, &$view)
    {
        $newTransition = new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'from__' . $stateFrom . '__to__' . $stateTo,
            WorkflowTransition::FIELD__TITLE => $title,
            WorkflowTransition::FIELD__DESCRIPTION => $description,
            WorkflowTransition::FIELD__STATE_FROM => $stateFrom,
            WorkflowTransition::FIELD__STATE_TO => $stateTo
        ]);
        $repo->create($newTransition);
        $view = $template->render(['transition' => $newTransition]) . $view;
    }
}
