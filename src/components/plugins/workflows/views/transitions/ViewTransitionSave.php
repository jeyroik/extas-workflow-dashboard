<?php
namespace extas\components\plugins\workflows\views\transitions;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionSample;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionSample;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewTransitionSave
 *
 * @method IRepository workflowTransitions()
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
        $repo = $this->workflowTransitions();
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
     * @param ITransition|null $currentTransition
     * @param $repo
     * @param $itemTemplate
     * @return string
     */
    protected function buildView(array $transitions, ?ITransition $currentTransition, $repo, $itemTemplate): string
    {
        $itemsView = '';
        foreach ($transitions as $index => $transition) {
            if ($currentTransition && ($transition->getName() == $currentTransition->getName())) {
                $transition = $currentTransition->buildFromSample(
                    $this->extractData($currentTransition->getName(), $currentTransition),
                    ''
                );
                $repo->update($transition);
                $this->updated = true;
            }
            $itemsView .= $itemTemplate->render(['transition' => $transition]);
        }

        return $itemsView;
    }

    /**
     * @param string $name
     * @param ITransition $transition
     * @return ITransitionSample
     */
    protected function extractData(string $name, ITransition $transition = null): ITransitionSample
    {
        $transitionData = $transition ? $transition->__toArray() : [];
        $defaults = array_merge($transitionData, [
            Transition::FIELD__TITLE => '',
            Transition::FIELD__DESCRIPTION => '',
            Transition::FIELD__STATE_FROM => '',
            Transition::FIELD__STATE_TO => '',
            Transition::FIELD__SCHEMA_NAME => ''
        ]);

        return new TransitionSample([
            Transition::FIELD__NAME => $name,
            Transition::FIELD__TITLE => $_REQUEST['title'] ?? $defaults[Transition::FIELD__TITLE],
            Transition::FIELD__DESCRIPTION => $_REQUEST['description'] ?? $defaults[Transition::FIELD__DESCRIPTION],
            Transition::FIELD__STATE_FROM => $_REQUEST['state_from'] ?? $defaults[Transition::FIELD__STATE_FROM],
            Transition::FIELD__STATE_TO => $_REQUEST['state_to'] ?? $defaults[Transition::FIELD__STATE_TO],
            Transition::FIELD__SCHEMA_NAME => $_REQUEST['schema_name'] ?? $defaults[Transition::FIELD__SCHEMA_NAME],
        ]);
    }
}
