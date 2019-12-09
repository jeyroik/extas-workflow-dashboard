<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\expands\PluginExpandAbstract;
use extas\components\SystemContainer;
use extas\interfaces\expands\IExpandingBox;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;
use extas\interfaces\workflows\schemas\IWorkflowSchema;
use extas\interfaces\workflows\transitions\IWorkflowTransition;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;

/**
 * Class SchemaExpandByTransitions
 *
 * @stage expand.index.schema
 * @package extas\components\plugins\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaExpandByTransitions extends PluginExpandAbstract
{
    /**
     * @param IExpandingBox $parent
     * @param IServerRequest $request
     * @param IServerResponse $response
     */
    protected function dispatch(IExpandingBox &$parent, IServerRequest &$request, IServerResponse &$response)
    {
        /**
         * @var $schemas
         * @var $transitRepo IWorkflowTransitionRepository
         * @var $transitions IWorkflowTransition[]
         */
        $schemasIndex = $parent->getData();
        $schemas = $schemasIndex['schemas'] ?? [];
        $transitionsNames = [];
        foreach ($schemas as $schema) {
            $transitionsNames = array_merge($transitionsNames, $schema[IWorkflowSchema::FIELD__TRANSITIONS]);
        }

        $transitRepo = SystemContainer::getItem(IWorkflowTransitionRepository::class);
        $transitions = $transitRepo->all([IWorkflowTransition::FIELD__NAME => $transitionsNames]);
        $transitionsByName = [];
        foreach ($transitions as $transition) {
            $transitionsByName[$transition->getName()] = $transition->__toArray();
        }

        foreach ($schemas as &$schema) {
            foreach ($schema[IWorkflowSchema::FIELD__TRANSITIONS] as $index => $transition) {
                $schema[IWorkflowSchema::FIELD__TRANSITIONS][$index] = $transitionsByName[$transition] ?? [
                    IWorkflowTransition::FIELD__NAME => $transition,
                    IWorkflowTransition::FIELD__TITLE => 'Ошибка: Неизвестный переход [' . $transition . ']'
                ];
            }
        }

        $parent->addToValue('schemas', $schemas);
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getExpandName(): string
    {
        return 'transitions';
    }
}
