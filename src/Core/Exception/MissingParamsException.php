<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MissingParamsException extends HttpException
{
    public function __construct($message = 'Missing parameter')
    {
        parent::__construct(400, $message);
    }
}
