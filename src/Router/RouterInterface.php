<?php
declare(strict_types=1);

namespace ApTeles\Router;

interface RouterInterface
{
    public function add(string $method, string $pattern, callable $callback): void;

    public function run();
}
