<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AppException extends HttpException
{
    public function __construct(string $message = 'Internal server error')
    {
        parent::__construct(500, $message);
    }

    public function getStatusCode(): int
    {
        return 500;
    }

    public function getHeaders(): array
    {
        return [];
    }
}
