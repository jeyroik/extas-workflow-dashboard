<?php
namespace tests\plugins\workflows\views\states;

use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\states\ViewStatesIndex;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewStatesIndexTest
 *
 * @author jeyroik@gmail.com
 */
class ViewStatesIndexTest extends TestCase
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

    public function testStatesIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->getMagicClass('workflowStates')->create(new State([
            State::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewStatesIndex();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Состояния</title>') !== false);
    }
}
