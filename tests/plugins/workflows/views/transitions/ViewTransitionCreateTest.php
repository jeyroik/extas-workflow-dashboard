<?php
namespace tests\plugins\workflows\views\transitions;

use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\transitions\ViewTransitionCreate;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\workflows\transitions\Transition;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewTransitionCreateTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionCreateTest extends TestCase
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
            ['workflowTransitions', 'name', Transition::class],
            ['workflowStates', 'name', State::class],
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testTransitionsIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewTransitionCreate();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);
    }
}
