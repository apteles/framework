<?php
declare(strict_types=1);

namespace ApTeles\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Responder implements ResponderInterface
{
    public function __invoke($action, $params)
    {
        $this->mergeStreamInputWithGlobalPost();

        $request = $this->createRequest($params);

        print \call_user_func($action, [$request, new Response()]);
    }


    private function mergeStreamInputWithGlobalPost(): void
    {
        \parse_str(\file_get_contents('php://input'), $_POST);
    }

    private function createRequest(array $data = []): Request
    {
        return new Request(
            $_GET,
            $_POST,
            \array_merge([], $data),
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }
}
