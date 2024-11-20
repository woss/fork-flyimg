<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceUnavailableException extends HttpException
{
    public function __construct($message = "The server is temporarily unable to process requests")
    {
        parent::__construct(503, $message);
    }
}
