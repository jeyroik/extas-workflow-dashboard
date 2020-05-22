<?php
namespace tests\plugins\workflows\views\transitions;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\transitions\ViewTransitionSave;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\interfaces\repositories\IRepository;

/**
 * Class ViewTransitionSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionSaveTest extends TestCase
{
    use TSnuffExtensions;
    use TSnuffHttp;

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
        $this->transitionRepo->delete([Transition::FIELD__TITLE => 'test']);
    }

    public function testTransitionUpdate()
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

        $dispatcher = new ViewTransitionSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);

        /**
         * @var Transition $transition
         */
        $transition = $this->transitionRepo->one([Transition::FIELD__NAME => 'test']);
        $this->assertEquals('test', $transition->getDescription());
    }

    public function testTransitionCreateOnUpdateIfNotExists()
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

        $dispatcher = new ViewTransitionSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $dispatcher($request, $response, ['name' => 'unknown']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);
        $this->assertNotEmpty($this->transitionRepo->one([Transition::FIELD__DESCRIPTION => 'test']));
    }
}
