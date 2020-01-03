<?php
declare(strict_types=1);

namespace ApTeles\Router;

use Exception;
use Invoker\Invoker;
use RuntimeException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use ApTeles\Router\Exceptions\HttpException;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Http\Message\ServerRequestInterface;
use Invoker\ParameterResolver\TypeHintResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;

class Router implements RouterInterface
{
    private const HOME = '/';

    private $routes = [];

    private $container = null;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
            $params = $this->parseUriRegexPattern($route, $this->uri());

            if (!$this->container) {
                $invoker = new Invoker(null, $this->container);

                return [
                    'invoker' => $invoker,
                    'params' => $params,
                    'action' => $action
                ];
            }

            $resolvers = [
                new AssociativeArrayResolver(),
                new TypeHintResolver($this->container),
                new DefaultValueResolver,
            ];

            $invoker = new Invoker(new ResolverChain($resolvers), $this->container);

            return [
                'invoker' => new ControllerInvoker(
                    $invoker,
                    $this->container->get(ServerRequestInterface::class),
                    $this->container->get(ResponseInterface::class)
                ),
                'params' => $params,
                'action' => $action];
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
        \preg_match($route, $uri, $params);

        if ($this->extractOnlyParams($params)) {
            return $this->extractOnlyParams($params);
        }
        return [];
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
