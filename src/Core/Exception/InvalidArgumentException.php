<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidArgumentException extends HttpException
{
    public function __construct($message = 'Invalid argument')
    {
        parent::__construct(400, $message);
    }
}
