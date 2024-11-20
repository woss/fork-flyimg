<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    public function __construct($message = "The server cannot process the request
     due to syntax errors, invalid data, or other errors on the client side.")
    {
        parent::__construct(401, $message);
    }
}
