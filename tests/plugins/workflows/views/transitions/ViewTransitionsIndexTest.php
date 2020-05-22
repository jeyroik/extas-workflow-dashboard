<?php
namespace tests\plugins\workflows\views\transitions;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\transitions\ViewTransitionsIndex;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewTransitionsIndexTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionsIndexTest extends TestCase
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
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->transitionRepo = new TransitionRepository();
        $this->addReposForExt([ITransitionRepository::class => TransitionRepository::class]);
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->deleteSnuffExtensions();
    }

    public function testTransitionsIndex()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test'
        ]));

        $dispatcher = new ViewTransitionsIndex();
        $dispatcher($request, $response, []);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);
    }
}
