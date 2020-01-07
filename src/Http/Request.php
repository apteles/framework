<?php
declare(strict_types=1);

namespace ApTeles\Http;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class Request extends HttpFoundationRequest implements RequestInterface
{
}
