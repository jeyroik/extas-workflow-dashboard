<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionSample;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionSample;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionSave
 *
 * @method workflowTransitionRepository()
 *
 * @stage view.transitions.save
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewTransitionSave extends Plugin
{
    use TItemsView;

    protected bool $updated = false;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $transitions ITransition[]
         */
        $repo = $this->workflowTransitionRepository();
        $transitions = $repo->all([]);
        $itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'transitions/item']);
        $transitionName = $args['name'] ?? '';
        $transition = $repo->one([ITransition::FIELD__NAME => $transitionName]);
        $itemsView = $this->buildView($transitions, $transition, $repo, $itemTemplate);

        if (!$this->updated) {
            $transitionSample = $this->extractData($transitionName);
            $newTransition = new Transition();
            $newTransition->buildFromSample($transitionSample);
            $newTransition = $repo->create($newTransition);
            $itemsView = $itemTemplate->render(['transition' => $newTransition]) . $itemsView;
        }
        $this->renderPage($itemsView, $response, 'transitions', 'Переходы');
    }

    /**
     * @param $transitions
     * @param $currentTransition
     * @param $repo
     * @param $itemTemplate
     * @return string
     */
    protected function buildView($transitions, $currentTransition, $repo, $itemTemplate): string
    {
        $itemsView = '';
        foreach ($transitions as $index => $transition) {
            if ($transition->getName() == $currentTransition->getName()) {
                $repo->update($transition);
                $this->updated = true;
            }
            $itemsView .= $itemTemplate->render(['transition' => $transition]);
        }

        return $itemsView;
    }

    /**
     * @param string $name
     * @return ITransitionSample
     */
    protected function extractData(string $name): ITransitionSample
    {
        return new TransitionSample([
            Transition::FIELD__NAME => $name,
            Transition::FIELD__TITLE => $_REQUEST['title'] ?? '',
            Transition::FIELD__DESCRIPTION => $_REQUEST['description'] ?? '',
            Transition::FIELD__STATE_FROM => $_REQUEST['state_from'] ?? '',
            Transition::FIELD__STATE_TO => $_REQUEST['state_to'] ?? ''
        ]);
    }
}
