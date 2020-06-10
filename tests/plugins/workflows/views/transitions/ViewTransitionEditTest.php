<?php
namespace tests\plugins\workflows\views\transitions;

use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepository;
use extas\components\plugins\workflows\views\transitions\ViewTransitionEdit;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\transitions\Transition;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewTransitionEditTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionEditTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->registerSnuffRepos([
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowStateRepository' => StateRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
    }

    public function testTransitionsIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'test'
        ]));
        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
            Transition::FIELD__NAME => 'test2',
            Transition::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewTransitionEdit();
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);
    }
}
