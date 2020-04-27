<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\plugins\workflows\jsonrpc\before\transitions\BeforeTransitionUpdate;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\interfaces\workflows\states\IStateRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;
use Slim\Http\Response as PsrResponse;

/**
 * Class BeforeTransitionUpdateTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeTransitionUpdateTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $transitionRepo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->transitionRepo = new TransitionRepository();
        $this->stateRepo = new StateRepository();

        SystemContainer::addItem(
            ITransitionRepository::class,
            TransitionRepository::class
        );
        SystemContainer::addItem(
            IStateRepository::class,
            StateRepository::class
        );
    }

    public function tearDown(): void
    {
        $this->transitionRepo->delete([Transition::FIELD__NAME => 'test']);
        $this->stateRepo->delete([State::FIELD__TITLE => 'test']);
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
    public function testTheSameFromToStates()
    {
        $operation = new BeforeTransitionUpdate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'test',
                Transition::FIELD__STATE_TO => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    /**
     * @throws
     */
    public function testUnknownStateFrom()
    {
        $operation = new BeforeTransitionUpdate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'unknown',
                Transition::FIELD__STATE_TO => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test',
            State::FIELD__TITLE => 'test'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertTrue($jsonRpcResponse->hasError());
    }

    /**
     * @throws
     */
    public function testUnknownStateTo()
    {
        $operation = new BeforeTransitionUpdate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'test',
                Transition::FIELD__STATE_TO => 'unknown'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'test',
            State::FIELD__TITLE => 'test'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

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
        $operation = new BeforeTransitionUpdate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'from',
                Transition::FIELD__STATE_TO => 'to'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'from',
            State::FIELD__TITLE => 'test'
        ]));

        $this->stateRepo->create(new State([
            State::FIELD__NAME => 'to',
            State::FIELD__TITLE => 'test'
        ]));

        $operation(
            $serverRequest,
            $serverResponse
        );

        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->assertFalse($jsonRpcResponse->hasError());
    }
}
