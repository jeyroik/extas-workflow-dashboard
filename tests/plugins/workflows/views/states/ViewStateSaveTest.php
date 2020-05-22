<?php
namespace tests\plugins\workflows\views\states;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\states\ViewStateSave;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewStateSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStateSaveTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffExtensions;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->stateRepo = new StateRepository();
        $this->addReposForExt(['workflowStateRepository' => StateRepository::class]);
    }

    public function tearDown(): void
    {
        $this->stateRepo->delete([State::FIELD__TITLE => 'test']);
        $this->deleteSnuffExtensions();
    }

    public function testStateUpdate()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test',
            State::FIELD__TITLE => 'test'
        ]));
        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test2',
            State::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewStateSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);

        /**
         * @var State $state
         */
        $state = $this->stateRepo->one([State::FIELD__NAME => 'test']);
        $this->assertEquals('test', $state->getDescription());
    }

    public function testStateCreateOnUpdateIfNotExists()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test',
            State::FIELD__TITLE => 'test'
        ]));
        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test2',
            State::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewStateSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'unknown']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
        $this->assertNotEmpty($this->stateRepo->one([State::FIELD__DESCRIPTION => 'test']));
    }
}
