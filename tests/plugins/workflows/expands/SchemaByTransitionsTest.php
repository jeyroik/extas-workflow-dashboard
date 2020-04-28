<?php
namespace tests;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\components\plugins\workflows\expands\schemas\SchemaExpandByTransitions;
use extas\components\workflows\schemas\Schema;
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
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->transitionRepo = new TransitionRepository();

        SystemContainer::addItem(
            ITransitionRepository::class,
            TransitionRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
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
        $operation = new SchemaExpandByTransitions();
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
    public function testValid()
    {
        $operation = new SchemaExpandByTransitions();
        $serverRequest = $this->getServerRequest();
        $serverResponse = $this->getServerResponse();
        $parent = new ExpandingBox([
            ExpandingBox::FIELD__NAME => 'schema',
            ExpandingBox::DATA__MARKER . 'schema' => [],
            ExpandingBox::FIELD__VALUE => [
                'schemas' => [
                    [
                        Schema::FIELD__NAME => 'test',
                        Schema::FIELD__TRANSITIONS_NAMES => ['test']
                    ]
                ]
            ]
        ]);

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'test',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $operation($parent, $serverRequest, $serverResponse);

        $this->assertEquals(
            ['schemas' => [
                [
                    Schema::FIELD__NAME => 'test',
                    Schema::FIELD__TRANSITIONS_NAMES => ['test'],
                    'transitions' => [
                        [
                            Transition::FIELD__NAME => 'test',
                            Transition::FIELD__TITLE => 'test',
                            Transition::FIELD__SCHEMA_NAME => 'test'
                        ]
                    ]
                ]
            ]],
            $parent->getValue()
        );
    }
}
