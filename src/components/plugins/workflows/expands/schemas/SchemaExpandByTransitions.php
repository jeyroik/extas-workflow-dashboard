<?php
namespace extas\components\plugins\workflows\expands\schemas;

use extas\components\plugins\expands\PluginExpandAbstract;
use extas\components\SystemContainer;
use extas\interfaces\expands\IExpandingBox;
use extas\interfaces\servers\requests\IServerRequest;
use extas\interfaces\servers\responses\IServerResponse;
use extas\interfaces\workflows\schemas\ISchema;
use extas\interfaces\workflows\transitions\ITransition;
use extas\interfaces\workflows\transitions\ITransitionRepository;

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
         * @var $transitRepo ITransitionRepository
         * @var $transitions ITransition[]
         */
        $value = $parent->getValue([]);
        if (empty($value)) {
            $schemasIndex = $parent->getData();
            $schemas = $schemasIndex['schemas'] ?? [];
        } else {
            $schemas = $value['schemas'];
        }
        $transitionsNames = [];
        foreach ($schemas as $schema) {
            $transitionsNames = array_merge($transitionsNames, $schema[ISchema::FIELD__TRANSITIONS_NAMES]);
        }

        $transitRepo = SystemContainer::getItem(ITransitionRepository::class);
        $transitions = $transitRepo->all([ITransition::FIELD__NAME => $transitionsNames]);
        $transitionsByName = [];
        foreach ($transitions as $transition) {
            $transitionsByName[$transition->getName()] = $transition->__toArray();
        }

        foreach ($schemas as &$schema) {
            foreach ($schema[ISchema::FIELD__TRANSITIONS_NAMES] as $index => $transition) {
                $schema[ISchema::FIELD__TRANSITIONS_NAMES][$index] = $transitionsByName[$transition] ?? [
                    ITransition::FIELD__NAME => $transition,
                    ITransition::FIELD__TITLE => 'Ошибка: Неизвестный переход [' . $transition . ']'
                ];
            }
        }

        $parent->addToValue('schemas', $schemas);
    }

    /**
     * @return string
     */
    protected function getExpandName(): string
    {
        return 'transitions';
    }
}
