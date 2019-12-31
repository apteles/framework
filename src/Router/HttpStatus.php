<?php
declare(strict_types=1);

namespace ApTeles\Router;

final class HttpStatus
{
    public const OK = 200;

    public const CREATED = 201;

    public const BAD_REQUEST = 400;

    public const UNAUTHORIZED = 401;

    public const FORBIDDEN = 403;

    public const NOT_FOUND = 404;

    public const INTERNAL_SERVER_ERROR = 500;

    private function __construct()
    {
    }
}
