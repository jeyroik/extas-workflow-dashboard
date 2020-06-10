<?php
namespace tests;

use extas\components\repositories\TSnuffRepository;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntityRepository;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntity;
use extas\components\workflows\schemas\Schema;
use extas\components\expands\ExpandingBox;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class SchemaByEntityTest
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class SchemaByEntityTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->registerSnuffRepos(['workflowEntityRepository' => EntityRepository::class]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
    }

    /**
     * @throws
     */
    public function testEmptyValue()
    {
        $operation = new SchemaExpandByEntity();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => []
        ]);

        $operation(
            $parent,
            $this->getPsrRequest('.schema.index', ['x-extas-expand' => 'schema.entity']),
            $this->getPsrResponse()
        );

        $this->assertEquals(['schemas' => []], $parent->getValue(), print_r($parent, true));
    }

    /**
     * @throws
     */
    public function testUnknown()
    {
        $operation = new SchemaExpandByEntity();
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

        $operation(
            $parent,
            $this->getPsrRequest('.schema.index', ['x-extas-expand' => 'schema.entity']),
            $this->getPsrResponse()
        );

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

        $this->createWithSnuffRepo('workflowEntityRepository', new Entity([
            Entity::FIELD__NAME => 'test',
            Entity::FIELD__TITLE => 'test'
        ]));

        $operation(
            $parent,
            $this->getPsrRequest('.schema.index', ['x-extas-expand' => 'schema.entity']),
            $this->getPsrResponse()
        );

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
