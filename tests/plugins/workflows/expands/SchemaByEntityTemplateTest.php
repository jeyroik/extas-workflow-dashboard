<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\entities\EntitySample;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\interfaces\workflows\entities\IEntitySampleRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntityTemplate;
use extas\components\workflows\schemas\Schema;
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

        $this->templateRepo = new EntitySampleRepository();

        SystemContainer::addItem(
            IEntitySampleRepository::class,
            EntitySampleRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->templateRepo->delete([EntitySample::FIELD__NAME => 'test']);
    }

    protected function getServerRequest()
    {
        return new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => ServerRequest::PARAMETER__EXPAND,
                    IParameter::FIELD__VALUE => 'schema.entity'
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
                        Schema::FIELD__ENTITY_TEMPLATE => 'unknown'
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
                    Schema::FIELD__ENTITY_TEMPLATE => [
                        EntitySample::FIELD__NAME => 'unknown',
                        EntitySample::FIELD__TITLE => 'Ошибка: Неизвестный шаблон сущности [unknown]'
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
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => [],
            ExpandingBox::FIELD__VALUE => [
                'schemas' => [
                    [
                        Schema::FIELD__ENTITY_TEMPLATE => 'test'
                    ]
                ]
            ]
        ]);

        $this->templateRepo->create(new EntitySample([
            EntitySample::FIELD__NAME => 'test',
            EntitySample::FIELD__TITLE => 'test'
        ]));

        $operation(
            $parent,
            $serverRequest,
            $serverResponse
        );

        $this->assertEquals(
            ['schemas' => [
                [
                    Schema::FIELD__ENTITY_TEMPLATE => [
                        EntitySample::FIELD__NAME => 'test',
                        EntitySample::FIELD__TITLE => 'test'
                    ]
                ]
            ]],
            $parent->getValue()
        );
    }
}
