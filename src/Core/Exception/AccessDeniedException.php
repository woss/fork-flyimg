<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AccessDeniedException extends HttpException
{
    public function __construct($message = "Access denied")
    {
        parent::__construct(403, $message);
    }
}
