<?php
declare(strict_types=1);

namespace ApTeles\Router\Exceptions;

use Exception;
use ApTeles\Router\HttpStatus;

class HttpException extends Exception
{
    public function __construct(string $message, $code = HttpStatus::OK, Exception $previous = null)
    {
        \http_response_code($code);
        parent::__construct($message, $code, $previous);
    }
}
