<?php
namespace tests\plugins\workflows\expands\schemas;

use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByTransitions;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\schemas\Schema;
use Dotenv\Dotenv;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\workflows\transitions\ITransition;
use PHPUnit\Framework\TestCase;

/**
 * Class SchemaByTransitionsTest
 *
 * @package tests\plugins\workflows\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaByTransitionsTest extends TestCase
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
            ['workflowTransitions', 'name', Transition::class]
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
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));
        $operation = new SchemaExpandByTransitions();
        $schema = new Schema([
            Schema::FIELD__NAME => 'test'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.schema.index',
                ['x-extas-expand' => 'schema.transitions']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'schema.transitions'
            ]
        ]);

        $operation($schema, $expand);

        $this->assertEquals(
            [
                [
                    ITransition::FIELD__NAME => 'test',
                    ITransition::FIELD__SCHEMA_NAME => 'test'
                ]
            ],
            $schema->__toArray()['transitions'], print_r($schema, true)
        );
    }
}
