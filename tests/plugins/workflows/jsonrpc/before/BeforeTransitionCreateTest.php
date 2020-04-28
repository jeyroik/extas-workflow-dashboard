<?php
namespace tests;

use Dotenv\Dotenv;
use extas\interfaces\servers\responses\IServerResponse;
use PHPUnit\Framework\TestCase;
use extas\interfaces\repositories\IRepository;
use extas\components\SystemContainer;
use extas\components\workflows\transitions\Transition;
use extas\components\workflows\transitions\TransitionRepository;
use extas\interfaces\workflows\transitions\ITransitionRepository;
use extas\interfaces\parameters\IParameter;
use extas\components\plugins\workflows\jsonrpc\before\transitions\BeforeTransitionCreate;
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
 * Class BeforeTransitionCreateTest
 *
 * @author jeyroik@gmail.com
 */
class BeforeTransitionCreateTest extends TestCase
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
        $env = Dotenv::create(getcwd() . '/tests/');
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
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'test',
                Transition::FIELD__STATE_TO => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $operation($serverRequest, $serverResponse);
        $this->hasError($serverResponse, 'True');
    }

    /**
     * @throws
     */
    public function testUnknownStateFrom()
    {
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'unknown',
                Transition::FIELD__STATE_TO => 'test'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->installState();

        $operation($serverRequest, $serverResponse);
        $this->hasError($serverResponse, 'True');
    }

    /**
     * @throws
     */
    public function testUnknownStateTo()
    {
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'test',
                Transition::FIELD__STATE_TO => 'unknown'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->installState();

        $operation($serverRequest, $serverResponse);
        $this->hasError($serverResponse, 'True');
    }

    /**
     * @throws
     */
    public function testValid()
    {
        $operation = new BeforeTransitionCreate();
        $serverRequest = $this->getServerRequest([
            'data' => [
                Transition::FIELD__STATE_FROM => 'from',
                Transition::FIELD__STATE_TO => 'to'
            ]
        ]);
        $serverResponse = $this->getServerResponse();

        $this->installState('from');
        $this->installState('to');

        $operation($serverRequest, $serverResponse);
        $this->hasError($serverResponse, 'False');
    }

    /**
     * @param string $name
     */
    protected function installState(string $name = 'test')
    {
        $this->stateRepo->create(new State([
            State::FIELD__NAME => $name,
            State::FIELD__TITLE => 'test'
        ]));
    }

    /**
     * @param IServerResponse $serverResponse
     * @param string $assert
     */
    protected function hasError(IServerResponse $serverResponse, string $assert = 'False')
    {
        /**
         * @var $jsonRpcResponse IResponse
         */
        $jsonRpcResponse = $serverResponse->getParameter(IResponse::SUBJECT)->getValue();
        $this->{'assert' . $assert}($jsonRpcResponse->hasError());
    }
}