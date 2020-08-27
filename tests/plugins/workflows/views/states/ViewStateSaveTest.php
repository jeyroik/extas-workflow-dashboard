<?php
namespace tests\plugins\workflows\views\states;

use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\states\ViewStateSave;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewStateSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStateSaveTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->createSnuffDynamicRepositories([
            ['workflowStates', 'name', State::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testStateUpdate()
    {
        list($request, $response, $dispatcher) = $this->prepare();

        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);

        /**
         * @var State $state
         */
        $state = $this->getMagicClass('workflowStates')->one([State::FIELD__NAME => 'test']);
        $this->assertEquals('test', $state->getDescription());
    }

    public function testStateCreateOnUpdateIfNotExists()
    {
        list($request, $response, $dispatcher) = $this->prepare();

        $dispatcher($request, $response, ['name' => 'unknown']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
        $this->assertNotEmpty(
            $this->getMagicClass('workflowStates')->one([State::FIELD__DESCRIPTION => 'test'])
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function prepare(): array
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'test',
            State::FIELD__TITLE => 'test'
        ]));
        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'test2',
            State::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewStateSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';

        return [$request, $response, $dispatcher];
    }
}
