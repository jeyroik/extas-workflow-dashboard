<?php
namespace tests;

use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\entities\Entity;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByEntity;
use extas\components\workflows\schemas\Schema;

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
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['workflowEntities', 'name', Entity::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $this->getMagicClass('workflowEntities')->create(new Entity([
            Entity::FIELD__NAME => 'test'
        ]));
        $operation = new SchemaExpandByEntity();
        $schema = new Schema([
            Schema::FIELD__ENTITY_NAME => 'test'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.schema.index',
                ['x-extas-expand' => 'schema.entity']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'schema.entity'
            ]
        ]);

        $operation($schema, $expand);

        $this->assertEquals(
            ['name' => 'test'],
            $schema->__toArray()[Schema::FIELD__ENTITY_NAME], print_r($schema, true)
        );
    }
}
