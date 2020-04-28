<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\workflows\schemas\Schema;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSample;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcherSampleRepository;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherSampleRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\workflows\transitions\dispatchers\TransitionDispatcher;
use extas\components\plugins\workflows\jsonrpc\before\transitions\dispatchers\BeforeTransitionDispatcherCreate;
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
 * Class BeforeTransitionDispatcherCreateTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeTransitionDispatcherCreateTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionTemplateRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $schemaRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->transitionRepo = new TransitionRepository();
        $this->transitionTemplateRepo = new TransitionDispatcherSampleRepository();
        $this->schemaRepo = new SchemaRepository();

        SystemContainer::addItem(
            ITransitionRepository::class,
            TransitionRepository::class
        );
        SystemContainer::addItem(
            ITransitionDispatcherSampleRepository::class,
            TransitionDispatcherSampleRepository::class
        );
        SystemContainer::addItem(
            ISchemaRepository::class,
            SchemaRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->transitionTemplateRepo->delete([TransitionDispatcherSample::FIELD__NAME => 'test']);
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
    public function testUnknownTransition()
    {
        $operation = new BeforeTransitionDispatcherCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                TransitionDispatcher::FIELD__NAME => 'test',
                TransitionDispatcher::FIELD__TRANSITION_NAME => 'unknown',
                TransitionDispatcher::FIELD__SAMPLE_NAME => 'test'
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
    public function testValid()
    {
        $operation = new BeforeTransitionDispatcherCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                TransitionDispatcher::FIELD__NAME => 'test',
                TransitionDispatcher::FIELD__TRANSITION_NAME => 'test',
                TransitionDispatcher::FIELD__SAMPLE_NAME => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->schemaRepo->create(new Schema([Schema::FIELD__NAME => 'test']));
        $this->transitionRepo->create(new Transition([Transition::FIELD__NAME => 'test']));

        $this->transitionTemplateRepo->create(new TransitionDispatcherSample([
            TransitionDispatcherSample::FIELD__NAME => 'test'
        ]));

        $operation($serverRequest, $serverResponse);

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());
    }
}
