<?php
namespace tests\plugins\workflows\views\states;

use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\states\ViewStateCreate;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewStateCreateTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStateCreateTest extends TestCase
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

    public function testStateCreateView()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewStateCreate();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
    }
}
