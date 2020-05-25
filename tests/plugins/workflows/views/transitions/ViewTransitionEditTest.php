<?php
namespace tests\plugins\workflows\views\transitions;

use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\transitions\ViewTransitionEdit;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewTransitionEditTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionEditTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffExtensions;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->transitionRepo = new TransitionRepository();
        $this->addReposForExt([
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowStateRepository' => StateRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([Transition::FIELD__TITLE => 'test']);
    }

    public function testTransitionsIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'test'
        ]));
        $this->transitionRepo->create(new Transition([
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
