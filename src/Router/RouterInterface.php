<?php
declare(strict_types=1);

namespace ApTeles\Router;

interface RouterInterface
{
    public function add(string $method, string $pattern, callable $callback, string $name = ''): void;

    public function run(string $httpMethod, string $currentURI);
}
