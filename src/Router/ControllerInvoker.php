<?php
declare(strict_types=1);

namespace ApTeles\Router;

use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerInvoker
{
    private $invoker;

    private $request;

    private $response;

    public function __construct(InvokerInterface $invoker, ServerRequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->invoker = $invoker;
        $this->request = $request;
        $this->response = $response;
    }

    public function __invoke(
        callable $callable,
        array $arguments
    ) :ResponseInterface {
        $parameters = [
            'request' => $this->request,
            'response' => $this->response
        ];

        $parameters += $arguments;
        $parameters += $this->request->getAttributes();

        return $this->invoker->call($callable, $parameters);
    }
}
