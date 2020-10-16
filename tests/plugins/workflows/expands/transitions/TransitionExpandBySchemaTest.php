<?php
namespace tests\plugins\workflows\expands\transitions;

use Dotenv\Dotenv;
use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\ExpandBySchema;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\schemas\Schema;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class TransitionExpandBySchemaTest
 *
 * @package tests\plugins\workflows\expands\transitions
 * @author jeyroik <jeyroik@gmail.com>
 */
class TransitionExpandBySchemaTest extends TestCase
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
            ['workflowSchemas', 'name', Schema::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testExpand()
    {
        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'schema',
            Schema::FIELD__TITLE => 'Schema'
        ]));
        $operation = new ExpandBySchema();
        $transition = new Transition([
            Transition::FIELD__SCHEMA_NAME => 'schema'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.transitions.index',
                ['x-extas-expand' => 'transition.schema']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'transition.schema'
            ]
        ]);

        $transition = $operation($transition, $expand);

        $this->assertEquals(
            [
                'schema_name' => 'schema',
                'schema' => [
                    'name' => 'schema',
                    'title' => 'Schema'
                ]
            ],
            $transition->__toArray(),
            'Incorrect expanding: ' . print_r($transition, true)
        );
    }
}
