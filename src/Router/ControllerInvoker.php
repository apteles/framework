<?php
declare(strict_types=1);

namespace ApTeles\Router;

use ApTeles\Http\RequestInterface;
use ApTeles\Http\ResponseInterface;
use Invoker\InvokerInterface;

class ControllerInvoker
{
    private $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function __invoke(
        callable $callable,
        RequestInterface $request = null,
        ResponseInterface $response = null
    ) {
        $parameters = [
            'request' => $request,
            'response' => $response
        ];

        return $this->invoker->call($callable, $parameters);
    }
}
