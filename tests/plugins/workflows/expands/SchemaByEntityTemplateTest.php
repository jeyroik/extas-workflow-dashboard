<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\entities\WorkflowEntityTemplate;
use extas\components\workflows\entities\WorkflowEntityTemplateRepository;
use extas\interfaces\workflows\entities\IWorkflowEntityTemplateRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntityTemplate;
use extas\components\workflows\schemas\WorkflowSchema;
use extas\components\expands\ExpandingBox;

class SchemaByEntityTemplateTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $templateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->templateRepo = new WorkflowEntityTemplateRepository();

        SystemContainer::addItem(
            IWorkflowEntityTemplateRepository::class,
            WorkflowEntityTemplateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->templateRepo->delete([WorkflowEntityTemplate::FIELD__NAME => 'test']);
    }

    protected function getServerRequest()
    {
        return new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => ServerRequest::PARAMETER__EXPAND,
                    IParameter::FIELD__VALUE => 'entity'
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
            ExpandingBox::FIELD__NAME => 'test',
            ExpandingBox::DATA__MARKER . 'test' => []
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
            ExpandingBox::FIELD__NAME => 'test',
            ExpandingBox::DATA__MARKER . 'test' => [],
            ExpandingBox::FIELD__VALUE => [
                'schemas' => [
                    [
                        WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'unknown'
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
                    WorkflowSchema::FIELD__ENTITY_TEMPLATE => [
                        WorkflowEntityTemplate::FIELD__NAME => 'unknown',
                        WorkflowEntityTemplate::FIELD__TITLE => 'Ошибка: Неизвестный шаблон сущности [unknown]'
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
                        WorkflowSchema::FIELD__ENTITY_TEMPLATE => 'test'
                    ]
                ]
            ]
        ]);

        $this->templateRepo->create([
            WorkflowEntityTemplate::FIELD__NAME => 'test',
            WorkflowEntityTemplate::FIELD__TITLE => 'test'
        ]);

        $operation(
            $parent,
            $serverRequest,
            $serverResponse
        );

        $this->assertEquals(
            ['schemas' => [
                [
                    WorkflowSchema::FIELD__ENTITY_TEMPLATE => [
                        WorkflowEntityTemplate::FIELD__NAME => 'test',
                        WorkflowEntityTemplate::FIELD__TITLE => 'test'
                    ]
                ]
            ]],
            $parent->getValue()
        );
    }
}
