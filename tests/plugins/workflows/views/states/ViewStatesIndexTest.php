<?php
namespace tests\plugins\workflows\views\states;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\states\ViewStatesIndex;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewStatesIndexTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStatesIndexTest extends TestCase
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
        $this->stateRepo->delete([State::FIELD__NAME => 'test']);
        $this->deleteSnuffExtensions();
    }

    public function testStatesIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewStatesIndex();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
    }
}
