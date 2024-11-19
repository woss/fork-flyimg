<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SecurityException extends HttpException
{
    public function __construct(string $message)
    {
         parent::__construct(403, $message);
    }
}
