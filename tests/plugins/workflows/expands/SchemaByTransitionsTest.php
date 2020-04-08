<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\WorkflowTransition;
use extas\components\workflows\transitions\WorkflowTransitionRepository;
use extas\interfaces\workflows\transitions\IWorkflowTransitionRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntityTemplate;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\expands\ExpandingBox;

class SchemaByTransitionsTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->transitionRepo = new WorkflowTransitionRepository();

        SystemContainer::addItem(
            IWorkflowTransitionRepository::class,
            WorkflowTransitionRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([WorkflowTransition::FIELD__NAME => 'test']);
    }

    protected function getServerRequest()
    {
        return new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => ServerRequest::PARAMETER__EXPAND,
                    IParameter::FIELD__VALUE => 'schema.transitions'
                ]
            ]
        ]);
    }

    protected function getServerResponse()
    {
        return new ServerResponse();
    }

    /**
     * @throws
     */
    public function testEmptyValue()
    {
        $operation = new SchemaExpandByEntityTemplate();
        $serverRequest = $this->getServerRequest();
        $serverResponse = $this->getServerResponse();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => []
        ]);

        $operation(
            $parent,
            $serverRequest,
            $serverResponse
        );

        $this->assertEquals(
            ['schemas' => []],
            $parent->getValue()
        );
    }

    /**
     * @throws
     */
    public function testUnknownTemplate()
    {
        $operation = new SchemaExpandByEntityTemplate();
        $serverRequest = $this->getServerRequest();
        $serverResponse = $this->getServerResponse();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => [],
            ExpandingBox::FIELD__VALUE => [
                'schemas' => [
                    [
                        WorkflowSchema::FIELD__TRANSITIONS => ['unknown']
                    ]
                ]
            ]
        ]);

        $operation(
            $parent,
            $serverRequest,
            $serverResponse
        );

        $this->assertEquals(
            ['schemas' => [
                [
                    WorkflowSchema::FIELD__TRANSITIONS => [
                        [
                            WorkflowTransition::FIELD__NAME => 'unknown',
                            WorkflowTransition::FIELD__TITLE => 'Ошибка: Неизвестный переход [unknown]'
                        ]
                    ]
                ]
            ]],
            $parent->getValue()
        );
    }

    /**
     * @throws
     */
    public function testValidTemplate()
    {
        $operation = new SchemaExpandByEntityTemplate();
        $serverRequest = $this->getServerRequest();
        $serverResponse = $this->getServerResponse();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'test',
            ExpandingBox::DATA__MARKER . 'test' => [],
            ExpandingBox::FIELD__VALUE => [
                'schemas' => [
                    [
                        WorkflowSchema::FIELD__TRANSITIONS => ['test']
                    ]
                ]
            ]
        ]);

        $this->transitionRepo->create(new WorkflowTransition([
            WorkflowTransition::FIELD__NAME => 'test',
            WorkflowTransition::FIELD__TITLE => 'test'
        ]));

        $operation(
            $parent,
            $serverRequest,
            $serverResponse
        );

        $this->assertEquals(
            ['schemas' => [
                [
                    WorkflowSchema::FIELD__TRANSITIONS => [
                        [
                            WorkflowTransition::FIELD__NAME => 'test',
                            WorkflowTransition::FIELD__TITLE => 'test'
                        ]
                    ]
                ]
            ]],
            $parent->getValue()
        );
    }
}
