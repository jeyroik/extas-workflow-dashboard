<?php
namespace tests;

use extas\components\http\TSnuffHttp;
use extas\components\repositories\TSnuffRepositoryDynamic;
use extas\components\THasMagicClass;
use extas\components\workflows\entities\Entity;
use extas\components\workflows\entities\EntitySample;
use extas\components\plugins\workflows\views\schemas\ViewSchemaEdit;
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
    use TSnuffRepositoryDynamic;
    use THasMagicClass;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        defined('APP__ROOT') || define('APP__ROOT', getcwd());

        $this->createSnuffDynamicRepositories([
            ['workflowSchemas', 'name', Schema::class],
            ['workflowTransitions', 'name', Transition::class],
            ['workflowEntitiesSamples', 'name', EntitySample::class],
            ['workflowEntities', 'name', Entity::class]
        ]);
    }

    public function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
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

        $this->getMagicClass('workflowSchemas')->create(new Schema([
            Schema::FIELD__NAME => 'test',
            Schema::FIELD__TITLE => 'Test',
            Schema::FIELD__DESCRIPTION => 'Test',
            Schema::FIELD__ENTITY_NAME => 'test'
        ]));
        $this->getMagicClass('workflowTransitions')->create(new Transition([
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
