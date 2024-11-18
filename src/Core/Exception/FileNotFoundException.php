<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FileNotFoundException extends HttpException
{
    public function __construct($message="File not found") {
        parent::__construct(404, $message);
    }
}
