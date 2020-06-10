<?php
namespace tests;

use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepository;
use extas\components\workflows\entities\EntityRepository;
use extas\components\workflows\entities\EntitySampleRepository;
use extas\components\plugins\workflows\views\schemas\ViewSchemaEdit;
use extas\components\workflows\schemas\SchemaRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\schemas\Schema;

use Psr\Http\Message\ResponseInterface;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

/**
 * Class ViewSchemaEditTest
 *
 * @author jeyroik@gmail.com
 */
class ViewSchemaEditTest extends TestCase
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
            'workflowSchemaRepository' => SchemaRepository::class,
            'workflowTransitionRepository' => TransitionRepository::class,
            'workflowEntitySampleRepository' => EntitySampleRepository::class,
            'workflowEntityRepository' => EntityRepository::class
        ]);
    }

    public function tearDown(): void
    {
        $this->unregisterSnuffRepos();
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
        $this->assertEquals(
            302,
            $response->getStatusCode(),
            'Incorrect response status: ' . $response->getStatusCode() . ', headers:'
            . json_encode($response->getHeaders())
        );
    }

    protected function runDispatcher(array $args): ResponseInterface
    {
        $request = $this->getPsrRequest();
        $response = $this->getPsrResponse();

        $this->createWithSnuffRepo('workflowSchemaRepository', new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->createWithSnuffRepo('workflowTransitionRepository', new Transition([
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
