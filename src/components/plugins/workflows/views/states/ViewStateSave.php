<?php
namespace extas\components\plugins\workflows\views\states;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\SystemContainer;
use extas\components\workflows\states\WorkflowState;
use extas\interfaces\workflows\states\IWorkflowState;
use extas\interfaces\workflows\states\IWorkflowStateRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ViewStateSave
 *
 * @stage view.states.save
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewStateSave extends Plugin
{
    /**
     * @param RequestInterface|ServerRequestInterface $request
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
        $itemsView = '';
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'states/item']);

        $stateName = $args['name'] ?? '';
        $stateTitle = $_REQUEST['title'] ?? '';
        $stateDesc = $_REQUEST['description'] ?? '';

        $updated = false;
        foreach ($states as $index => $state) {
            if ($state->getName() == $stateName) {
                $state->setTitle(htmlspecialchars($stateTitle))
                    ->setDescription(htmlspecialchars($stateDesc));
                $stateRepo->update($state);
                $updated = true;
            }
            $itemsView .= $itemTemplate->render(['state' => $state]);
        }

        if (!$updated) {
            $this->createState($stateTitle, $stateDesc, $itemTemplate, $stateRepo, $itemsView);
        }

        $listTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'states/index']);
        $listView = $listTemplate->render(['states' => $itemsView]);

        $pageTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'layouts/main']);
        $page = $pageTemplate->render([
            'page' => [
                'title' => 'Состояния',
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
     * @param DashboardView $template
     * @param IWorkflowStateRepository $repo
     * @param string $view
     */
    protected function createState($title, $description, $template, $repo, &$view)
    {
        $newState = new WorkflowState([
            WorkflowState::FIELD__NAME => uniqid('state_', true),
            WorkflowState::FIELD__TITLE => $title,
            WorkflowState::FIELD__DESCRIPTION => $description
        ]);
        $repo->create($newState);
        $view = $template->render(['state' => $newState]) . $view;
    }
}
