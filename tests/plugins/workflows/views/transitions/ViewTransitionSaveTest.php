<?php
namespace tests\plugins\workflows\views\transitions;

use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\states\State;
use extas\components\http\TSnuffHttp;
use extas\components\plugins\workflows\views\transitions\ViewTransitionSave;
use extas\components\workflows\transitions\Transition;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewTransitionSaveTest
 *
 * @author jeyroik@gmail.com
 */
class ViewTransitionSaveTest extends TestCase
{
    use TSnuffRepositoryDynamic;
    use THasMagicClass;
    use TSnuffHttp;

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

    public function testTransitionUpdate()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'test'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test2',
            Transition::FIELD__TITLE => 'test'
        ]));

        $dispatcher = new ViewTransitionSave();
        $_REQUEST['title'] = 'test';
        $_REQUEST['description'] = 'test';
        $_REQUEST['schema_name'] = 'test';
        $dispatcher($request, $response, ['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Переходы</title>') !== false);

        /**
         * @var Transition $transition
         */
        $transition = $this->getMagicClass('workflowTransitions')->one([Transition::FIELD__NAME => 'test']);
        $this->assertEquals(
            'test',
            $transition->getDescription(),
            'Wrong transition description.' . print_r($transition->__toArray(), true)
        );
    }

    public function testTransitionCreateOnUpdateIfNotExists()
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->getMagicClass('workflowTransitions')->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'test'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
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
        $this->assertNotEmpty(
            $this->getMagicClass('workflowTransitions')->one([Transition::FIELD__DESCRIPTION => 'test'])
        );
    }
}
