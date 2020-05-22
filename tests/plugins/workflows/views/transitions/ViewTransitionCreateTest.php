<?php
namespace tests\plugins\workflows\views\transitions;

use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\transitions\ViewTransitionCreate;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\states\IStateRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewTransitionCreateTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionCreateTest extends TestCase
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
            ITransitionRepository::class => TransitionRepository::class,
            IStateRepository::class => StateRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
    }

    public function testTransitionsIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewTransitionCreate();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);
    }
}
