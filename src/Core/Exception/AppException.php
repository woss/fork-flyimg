<?php

namespace Core\Exception;

class AppException extends \Exception
{
    public function getStatusCode(): int
    {
        return 500;
    }

    public function getHeaders(): array
    {
        return [];
    }
}
