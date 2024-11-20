<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    public function __construct($message = "The server cannot process the request \
        due to lacks valid authentication credentials for the requested resource.")
    {
        parent::__construct(401, $message);
    }
}
