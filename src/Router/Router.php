<?php
declare(strict_types=1);

namespace ApTeles\Router;

use Exception;
use Invoker\Invoker;
use RuntimeException;
use Psr\Container\ContainerInterface;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\TypeHintResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;

class Router implements RouterInterface
{
    private const HOME = '/';

    private $routes = [];

    private $container = null;

    private $currentPrefix = '';

    private $uri;

    private $currentMethod;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->currentPrefix;
        $this->currentPrefix = $previousPrefix . $prefix;
        $callback($this);
        $this->currentPrefix = $previousPrefix;
    }

    public function add(string $method, string $pattern, callable $callback, string $name = ''): void
    {
        $pattern = $this->currentPrefix . $pattern;

        $route = [
            'method' => $this->parseMethod($method),
            'route' => [
                'regex' => $this->transformUriTORegexPattern($pattern),
                'raw' => $this->transformUriTOStringFormatted($pattern)
            ],
            'name' => $name,
            'callback' => $callback
        ];

        $this->routes[] = $route;
    }

    public function getRoutes(): array
    {
        if (!$this->routes) {
            throw new Exception("Route not defined yet.");
        }
        return $this->routes;
    }

    public function route(string $name, array $params = [])
    {
        foreach ($this->routes as  $route) {
            if ($name === $route['name']) {
                return \vsprintf($route['route']['raw'], $params);
            }
        }
    }

    public function run(string $httpMethod, string $currentURI)
    {
        $this->setMethod($httpMethod);
        $this->setURI($currentURI);

        foreach ($this->getRoutes() as  $route) {
            [
                'method' => $httpVerb,
                'route' => $route,
                'callback' => $callback
            ] = $route;

            if ($this->parseMethod($httpVerb) === $this->parseMethod($httpMethod)) {
                $result = $this->parseUriRegexPattern($route['regex'], $this->uri());

                if (!$this->container && $result['isValidRoute']) {
                    $invoker = new Invoker(null, $this->container);

                    return [
                    'invoker' => $invoker,
                    'params' => $result['params'],
                    'action' => $callback
                ];
                }

                if ($result['isValidRoute']) {
                    $resolvers = [
                    new AssociativeArrayResolver(),
                    new TypeHintResolver($this->container),
                    new DefaultValueResolver,
                ];

                    $invoker = new Invoker(new ResolverChain($resolvers), $this->container);

                    return [
                    'invoker' => new ControllerInvoker($invoker),
                    'params' => $result['params'],
                    'action' => $callback];
                }
            }
        }

        throw new RuntimeException("Route {$this->uri()} not found.");
    }

    private function setURI(string $uri): void
    {
        $this->uri = $uri;
    }

    public function uri(): string
    {
        if (!$this->isHome($this->uri)) {
            $this->uri = \rtrim($this->uri, '/');
            return $this->uri;
        }
        return $this->uri;
    }

    private function setMethod(string $method): void
    {
        $this->currentMethod = $method;
    }

    public function isHome($url): bool
    {
        return $url === self::HOME;
    }

    public function transformUriTOStringFormatted($routeRaw):string
    {
        $rawRoute = \str_replace('(\d+)', '%d', $routeRaw);
        $rawRoute = \str_replace('(\w+)', '%s', $rawRoute);
        return $rawRoute;
    }

    private function transformUriTORegexPattern(string $string): string
    {
        return '/^' . \str_replace('/', '\/', $string) . '$/';
    }

    private function parseUriRegexPattern(string $route, string $uri): array
    {
        $isValidRoute = \preg_match($route, $uri, $params);

        if ($this->extractOnlyParams($params)) {
            return[
                'isValidRoute' => $isValidRoute,
                'params' => $this->extractOnlyParams($params)
            ];
        }
        return ['isValidRoute' => $isValidRoute, 'params' =>[]];
    }

    public function extractOnlyParams($params): array
    {
        \array_shift($params);
        return $params;
    }

    private function getCurrentMethodInRequest(): string
    {
        if ($this->currentMethod) {
            return $this->parseMethod($this->currentMethod);
        }
        throw new RuntimeException("Index 'REQUEST_METHOD' not defined in global server ");
    }

    private function parseMethod(string $method): string
    {
        return \strtolower($method);
    }
}
