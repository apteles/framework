<?php
declare(strict_types=1);

namespace ApTeles\Http;

use App\Module\Contracts\ViewInterface;
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


        $middleware = function ($request, $response) {
            $view = $this->container->get(ViewInterface::class);
            if (isset($_SESSION['old'])) {
                $view->addGlobal('old', $_SESSION['old']);
            }

            $_SESSION['old'] = $request->request->all();

            return $response;
        };

        $middleware2 = function ($request, $response) {
            $view = $this->container->get(ViewInterface::class);
            if (isset($_SESSION['messages'])) {
                $view->addGlobal('messages', $_SESSION['messages']);
                unset($_SESSION['messages']);
            }
            return $response;
        };

        
        $response = $middleware($request, $response);
        $response = $middleware2($request, $response);

        $response = $invoker($action, $request, $response);

        // $middleware = function ($request, $response) {
        //     var_dump($_SESSION['old']);
        //     return $response;
        // };
        //$response = $middleware($request, $response);

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
