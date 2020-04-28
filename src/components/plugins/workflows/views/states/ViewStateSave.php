<?php
namespace extas\components\plugins\workflows\views\states;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\components\SystemContainer;
use extas\components\workflows\states\State;
use extas\interfaces\workflows\states\IState;
use extas\interfaces\workflows\states\IStateRepository;
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
    use TItemsView;

    /**
     * @param RequestInterface|ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $stateRepo IStateRepository
         * @var $states IState[]
         */
        $stateRepo = SystemContainer::getItem(IStateRepository::class);
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
        $this->renderPage($itemsView, $response);
    }

    /**
     * @param string $title
     * @param string $description
     * @param DashboardView $template
     * @param IStateRepository $repo
     * @param string $view
     */
    protected function createState($title, $description, $template, $repo, &$view)
    {
        $newState = new State([
            State::FIELD__NAME => uniqid(),
            State::FIELD__TITLE => $title,
            State::FIELD__DESCRIPTION => $description
        ]);
        $repo->create($newState);
        $view = $template->render(['state' => $newState]) . $view;
    }
}
