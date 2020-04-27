<?php

use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\interfaces\parameters\IParameter;
use extas\components\workflows\transitions\Transition;
use extas\components\jsonrpc\transitions\TransitionLoad;
use extas\components\servers\requests\ServerRequest;
use extas\components\servers\responses\ServerResponse;
use extas\interfaces\jsonrpc\IRequest;
use extas\interfaces\jsonrpc\IResponse;
use extas\components\jsonrpc\Request;
use extas\components\jsonrpc\Response;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\states\IStateRepository;
use extas\components\workflows\states\StateRepository;
use extas\components\workflows\states\State;
use Slim\Http\Response as PsrResponse;

/**
 * Class TransitionLoadTest
 *
 * @author jeyroik@gmail.com
 */
class TransitionLoadTest extends TestCase
{
    /**
     * @var IRepository|null
     */
    protected ?IRepository $repo = null;

    /**
     * @var IRepository|null
     */
    protected ?IRepository $stateRepo = null;

    protected function setUp(): void
    {
        parent::setUp();
        $env = \Dotenv\Dotenv::create(getcwd() . '/tests/');
        $env->load();

        $this->repo = new TransitionRepository();
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
        $this->repo->delete([Transition::FIELD__TITLE => 'test']);
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
    public function testValid()
    {
        $operation = new TransitionLoad();
        $serverRequest = $this->getServerRequest([
            'data' => [
                [
                    Transition::FIELD__NAME => 'test',
                    Transition::FIELD__TITLE => 'test',
                    Transition::FIELD__STATE_FROM => 'from',
                    Transition::FIELD__STATE_FROM => 'to'
                ],
                [
                    Transition::FIELD__NAME => 'already-exists',
                    Transition::FIELD__TITLE => 'test',
                    Transition::FIELD__STATE_FROM => 'from',
                    Transition::FIELD__STATE_FROM => 'to'
                ],
                [
                    Transition::FIELD__NAME => 'test2',
                    Transition::FIELD__TITLE => 'test',
                    Transition::FIELD__STATE_FROM => 'unknown-state-from',
                    Transition::FIELD__STATE_FROM => 'to'
                ],
                [
                    Transition::FIELD__NAME => 'test2',
                    Transition::FIELD__TITLE => 'test',
                    Transition::FIELD__STATE_FROM => 'from',
                    Transition::FIELD__STATE_FROM => 'unknown-state-to'
                ]
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->repo->create(new Transition([
            Transition::FIELD__NAME => 'already-exists',
            Transition::FIELD__TITLE => 'test'
        ]));

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
        $this->assertEquals(
            [
                IResponse::RESPONSE__ID => $jsonRpcResponse->getData()[IRequest::FIELD__ID] ?? '',
                IResponse::RESPONSE__VERSION => IResponse::VERSION_CURRENT,
                IResponse::RESPONSE__RESULT => [
                    'created_count' => 1,
                    'got_count' => 4
                ]
            ],
            json_decode($jsonRpcResponse->getPsrResponse()->getBody(), true)
        );
    }
}
