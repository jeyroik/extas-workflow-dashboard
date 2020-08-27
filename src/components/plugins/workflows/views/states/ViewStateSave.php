<?php
namespace extas\components\plugins\workflows\views\states;

use extas\components\dashboards\DashboardView;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TItemsView;
use extas\components\workflows\states\State;
use extas\interfaces\dashboards\IDashboardView;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\states\IState;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ViewStateSave
 *
 * @method IRepository workflowStates()
 *
 * @stage view.states.save
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewStateSave extends Plugin
{
    use TItemsView;

    protected bool $updated = false;
    protected ?IDashboardView $itemTemplate = null;

    /**
     * ViewStateSave constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->itemTemplate = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'states/item']);
    }

    /**
     * @param RequestInterface|ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $states IState[]
         */
        $states = $this->workflowStates()->all([]);
        $stateName = $args['name'] ?? '';
        $stateTitle = $_REQUEST['title'] ?? '';
        $stateDesc = $_REQUEST['description'] ?? '';
        $itemsView = $this->buildView($states, $stateName, $stateTitle, $stateDesc);

        if (!$this->updated) {
            $newState = $this->createState($stateTitle, $stateDesc);
            $itemsView = $this->itemTemplate->render(['state' => $newState]) . $itemsView;
        }
        $this->renderPage($itemsView, $response);
    }

    /**
     * @param $states
     * @param $stateName
     * @param $stateTitle
     * @param $stateDesc
     * @return string
     */
    protected function buildView($states, $stateName, $stateTitle, $stateDesc): string
    {
        $itemsView = '';
        $repo = $this->workflowStates();
        foreach ($states as $index => $state) {
            if ($state->getName() == $stateName) {
                $state->setTitle(htmlspecialchars($stateTitle))
                    ->setDescription(htmlspecialchars($stateDesc));
                $repo->update($state);
                $this->updated = true;
            }
            $itemsView .= $this->itemTemplate->render(['state' => $state]);
        }

        return $itemsView;
    }

    /**
     * @param string $title
     * @param string $description
     * @return IState
     */
    protected function createState($title, $description): IState
    {
        $newState = new State([
            State::FIELD__NAME => uniqid(),
            State::FIELD__TITLE => $title,
            State::FIELD__DESCRIPTION => $description
        ]);
        return $this->workflowStates()->create($newState);
    }
}
