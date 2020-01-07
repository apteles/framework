<?php
declare(strict_types=1);

namespace ApTeles\Http;

use Psr\Container\ContainerInterface;

class Responder implements ResponderInterface
{
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function __invoke($invoker, $action, $params)
    {
        $this->mergeStreamInputWithGlobalPost();

        $request = $this->createRequest($params);
        $response = $this->createResponse();


        $response = $invoker($action, $request, $response);
        return $response->send();
    }


    private function mergeStreamInputWithGlobalPost(): void
    {
        \parse_str(\file_get_contents('php://input'), $_POST);
    }

    private function createRequest(array $data = []): RequestInterface
    {
        if (!$this->container->has(RequestInterface::class)) {
            $request = new Request(
                $_GET,
                $_POST,
                \array_merge([], $data),
                $_COOKIE,
                $_FILES,
                $_SERVER
            );


            $this->container->set(RequestInterface::class, $request);

            return $request;
        }
    }

    public function createResponse(): ResponseInterface
    {
        if (!$this->container->has(ResponseInterface::class)) {
            $response = new Response();

            $this->container->set(ResponseInterface::class, $response);

            return $response;
        }
    }
}
