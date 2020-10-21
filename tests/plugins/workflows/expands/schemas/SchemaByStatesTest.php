<?php
namespace tests\plugins\workflows\expands\schemas;

use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByStates;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\schemas\Schema;
use Dotenv\Dotenv;
use extas\components\workflows\states\State;
use extas\interfaces\workflows\states\IState;
use PHPUnit\Framework\TestCase;

/**
 * Class SchemaByStatesTest
 *
 * @package tests\plugins\workflows\expands\schemas
 * @author jeyroik@gmail.com
 */
class SchemaByStatesTest extends TestCase
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
            ['workflowStates', 'name', State::class]
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
        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'test',
            State::FIELD__SCHEMA_NAME => 'test'
        ]));
        $operation = new SchemaExpandByStates();
        $schema = new Schema([
            Schema::FIELD__NAME => 'test'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.schema.index',
                ['x-extas-expand' => 'schema.states']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'schema.states'
            ]
        ]);

        $operation($schema, $expand);

        $this->assertEquals(
            [
                [
                    IState::FIELD__NAME => 'test',
                    IState::FIELD__SCHEMA_NAME => 'test'
                ]
            ],
            $schema->__toArray()['states'], print_r($schema, true)
        );
    }
}
