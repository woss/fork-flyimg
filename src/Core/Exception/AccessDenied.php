<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AccessDenied extends HttpException
{
    public function __construct($message = "Access denied")
    {
        parent::__construct(403, $message);
    }
}
