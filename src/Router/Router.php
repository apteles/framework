<?php
declare(strict_types=1);

namespace ApTeles\Router;

use Exception;
use RuntimeException;
use ApTeles\Router\Exceptions\HttpException;

class Router implements RouterInterface
{
    private const HOME = '/';

    private $routes = [];

    public function add(string $method, string $pattern, callable $callback): void
    {
        $this->routes[$this->parseMethod($method)][$this->transformUriTORegexPattern($pattern)] = $callback;
    }

    public function getRoutes(): array
    {
        if (!$this->routes) {
            throw new Exception("Route not defined yet.");
        }
        return $this->routes[$this->getCurrentMethodInRequest()];
    }

    public function run()
    {
        foreach ($this->getRoutes() as $route => $action) {
            if ($params = $this->parseUriRegexPattern($route, $this->uri()) !== false) {
                return $action($params);
            }
        }

        throw new HttpException('Page not found.', HttpStatus::NOT_FOUND);
    }

    public function uri(): string
    {
        $currentURI = $_SERVER['PATH_INFO'] ?? self::HOME;

        if (!$this->isHome($currentURI)) {
            return \rtrim($currentURI, '/');
        }
        return $currentURI;
    }

    public function isHome($url): bool
    {
        return $url === self::HOME;
    }

    private function transformUriTORegexPattern(string $string): string
    {
        return '/^' . \str_replace('/', '\/', $string) . '$/';
    }

    private function parseUriRegexPattern(string $route, string $uri): array
    {
        if ($result = \preg_match($route, $uri, $params)) {
            return $this->extractOnlyParams($params);
        }

        return $result;
    }

    public function extractOnlyParams($params): array
    {
        \array_shift($params);
        return $params;
    }

    private function getCurrentMethodInRequest(): string
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return $this->parseMethod($_SERVER['REQUEST_METHOD']);
        }
        throw new RuntimeException("Index 'REQUEST_METHOD' not defined in global server ");
    }

    private function parseMethod(string $method): string
    {
        return \strtolower($method);
    }
}
