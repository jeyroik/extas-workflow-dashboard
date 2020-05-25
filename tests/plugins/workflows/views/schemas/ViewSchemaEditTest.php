<?php
namespace tests;

use Dotenv\Dotenv;
use extas\components\extensions\TSnuffExtensions;
use extas\components\http\TSnuffHttp;
use extas\components\workflows\entities\EntityRepository;
use extas\components\workflows\entities\EntitySampleRepository;
use PHPUnit\Framework\TestCase;
use extas\components\plugins\workflows\views\schemas\ViewSchemaEdit;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;
use extas\interfaces\repositories\IRepository;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewSchemaEditTest
 *
 * @author jeyroik@gmail.com
 */
class ViewSchemaEditTest extends TestCase
{
    use TSnuffHttp;
    use TSnuffExtensions;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

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

        $this->schemaRepo = new SchemaRepository();
        $this->transitionRepo = new TransitionRepository();
        $this->addReposForExt([
            'workflowSchemaRepository' => SchemaRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowEntitySampleRepository' => EntitySampleRepository::class,
            'workflowEntityRepository' => EntityRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->deleteSnuffExtensions();
    }

    public function testEditingSchema()
    {
        $response = $this->runDispatcher(['name' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());

        $page = (string) $response->getBody();
        $this->assertTrue(strpos($page, '<title>Схемы - Редактирование</title>') !== false);
    }

    public function testRedirectOnEmptySchema()
    {
        $response = $this->runDispatcher(['name' => 'unknown']);
        $this->assertEquals(302, $response->getStatusCode());
    }

    protected function runDispatcher(array $args): ResponseInterface
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->schemaRepo->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->transitionRepo->create(new Transition([
            Transition::FIELD__NAME => 'test',
            Transition::FIELD__TITLE => 'Test',
            Transition::FIELD__STATE_FROM => 'from',
            Transition::FIELD__STATE_TO => 'to',
            Transition::FIELD__SCHEMA_NAME => 'test'
        ]));

        $_REQUEST['transitions'] = 'test';
        $_REQUEST['entity_name'] = 'new';

        $dispatcher = new ViewSchemaEdit();

        $dispatcher($request, $response, $args);

        return $response;
    }
}
