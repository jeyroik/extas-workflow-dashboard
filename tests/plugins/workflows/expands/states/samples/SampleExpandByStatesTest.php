<?php
namespace tests\plugins\workflows\expands\states\samples;

use Dotenv\Dotenv;
use extas\components\expands\Expand;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\expands\states\samples\SampleExpandByStates;
use extas\components\plugins\workflows\expands\states\StateExpandByTransitions;
use extas\components\plugins\workflows\expands\transitions\TransitionExpandByStateFrom;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\workflows\states\StateSample;
use extas\components\workflows\transitions\Transition;
use PHPUnit\Framework\TestCase;

/**
 * Class SampleExpandByStatesTest
 *
 * @package tests\plugins\workflows\expands\states\samples
 * @author jeyroik <jeyroik@gmail.com>
 */
class SampleExpandByStatesTest extends TestCase
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

    public function testExpand()
    {
        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'state',
            State::FIELD__SAMPLE_NAME => 'test'
        ]));
        $operation = new SampleExpandByStates();
        $stateSample = new StateSample([
            StateSample::FIELD__NAME => 'test'
        ]);
        $expand = new Expand([
            Expand::FIELD__PSR_REQUEST => $this->getPsrRequest(
                '.state.sample.index',
                ['x-extas-expand' => 'sample.states']
            ),
            Expand::FIELD__PSR_RESPONSE => $this->getPsrResponse(),
            Expand::FIELD__ARGUMENTS => [
                'expand' => 'sample.states'
            ]
        ]);

        $stateSample = $operation($stateSample, $expand);

        $this->assertEquals(
            [
                'name' => 'test',
                'states' => [
                    [
                        'name' => 'state',
                        'sample_name' => 'test'
                    ]
                ]
            ],
            $stateSample->__toArray(),
            'Incorrect expanding: ' . print_r($stateSample->__toArray(), true)
        );
    }
}
