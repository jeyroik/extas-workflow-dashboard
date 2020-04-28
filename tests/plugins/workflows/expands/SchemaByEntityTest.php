<?php
namespace tests;

use Dotenv\Dotenv;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityRepository;
use extas\interfaces\workflows\entities\IEntityRepository;
use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\interfaces\parameters\IParameter;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntity;
use extas\components\workflows\schemas\Schema;
use extas\components\expands\ExpandingBox;

/**
 * Class SchemaByEntityTest
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class SchemaByEntityTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->stateRepo = new EntityRepository();

        SystemContainer::addItem(
            IEntityRepository::class,
            EntityRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->stateRepo->delete([Entity::FIELD__NAME => 'test']);
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
        $operation = new SchemaExpandByEntity();
        $serverRequest = $this->getServerRequest();
        $serverResponse = $this->getServerResponse();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => []
        ]);

        $operation($parent, $serverRequest, $serverResponse);

        $this->assertEquals(
            ['schemas' => []],
            $parent->getValue()
        );
    }

    /**
     * @throws
     */
    public function testUnknown()
    {
        $operation = new SchemaExpandByEntity();
        $serverRequest = $this->getServerRequest();
        $serverResponse = $this->getServerResponse();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => [],
            ExpandingBox::FIELD__VALUE => [
                'schemas' => [
                    [
                        Schema::FIELD__ENTITY_NAME => 'unknown'
                    ]
                ]
            ]
        ]);

        $operation($parent, $serverRequest, $serverResponse);

        $this->assertEquals(
            ['schemas' => [
                [
                    Schema::FIELD__ENTITY_NAME => [
                        Entity::FIELD__NAME => 'unknown',
                        Entity::FIELD__TITLE => 'Ошибка: Неизвестная сущность [unknown]'
                    ]
                ]
            ]],
            $parent->getValue()
        );
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new SchemaExpandByEntity();
        $serverRequest = $this->getServerRequest();
        $serverResponse = $this->getServerResponse();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => [],
            ExpandingBox::FIELD__VALUE => [
                'schemas' => [
                    [
                        Schema::FIELD__ENTITY_NAME => 'test'
                    ]
                ]
            ]
        ]);

        $this->stateRepo->create(new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__TITLE => 'test'
        ]));

        $operation($parent, $serverRequest, $serverResponse);

        $this->assertEquals(
            ['schemas' => [
                [
                    Schema::FIELD__ENTITY_NAME => [
                        Entity::FIELD__NAME => 'test',
                        Entity::FIELD__TITLE => 'test'
                    ]
                ]
            ]],
            $parent->getValue()
        );
    }
}
