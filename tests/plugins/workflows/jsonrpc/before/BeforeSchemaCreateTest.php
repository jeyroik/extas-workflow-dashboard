<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\SystemContainer;
use extas\interfaces\parameters\IParameter;
use extas\components\plugins\workflows\jsonrpc\before\schemas\BeforeSchemaCreate;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\components\workflows\schemas\SchemaRepository;
use extas\interfaces\workflows\schemas\ISchemaRepository;
use Slim\Http\Response as PsrResponse;

/**
 * Class BeforeSchemaCreateTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeSchemaCreateTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->schemaRepo = new SchemaRepository();

        SystemContainer::addItem(
            ISchemaRepository::class,
            SchemaRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->schemaRepo->delete([Schema::FIELD__NAME => 'test']);
    }

    protected function getServerRequest(array $params)
    {
        return new ServerRequest([
            ServerRequest::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => IRequest::SUBJECT,
                    IParameter::FIELD__VALUE => new Request([
                        IRequest::FIELD__PARAMS => $params
                    ])
                ]
            ]
        ]);
    }

    protected function getServerResponse()
    {
        return new ServerResponse([
            ServerResponse::FIELD__PARAMETERS => [
                [
                    IParameter::FIELD__NAME => IResponse::SUBJECT,
                    IParameter::FIELD__VALUE => new Response([
                        Response::FIELD__RESPONSE => new PsrResponse()
                    ])
                ]
            ]
        ]);
    }

    /**
     * @throws
     */
    public function testSchemaAlreadyExists()
    {
        $operation = new BeforeSchemaCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Schema::FIELD__NAME => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new Schema([Schema::FIELD__NAME => 'test']));

        $operation($serverRequest, $serverResponse);

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    /**
     * @throws
     */
    public function testValidSchema()
    {
        $operation = new BeforeSchemaCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Schema::FIELD__NAME => 'test',
                Schema::FIELD__TRANSITIONS_NAMES => []
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $operation($serverRequest, $serverResponse);

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());
    }
}
