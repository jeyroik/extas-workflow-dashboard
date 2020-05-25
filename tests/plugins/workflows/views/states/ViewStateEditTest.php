<?php
namespace tests\plugins\workflows\views\states;

use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\states\ViewStateEdit;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewStateEditTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStateEditTest extends TestCase
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
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
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

    public function testStateEdit()
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

        $dispatcher = new ViewStateEdit();
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
    }
}
