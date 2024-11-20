<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ReadFileException extends HttpException
{
    public function __construct()
    {
        parent::__construct(503, "The server is temporarily unable to process requests");
    }
}
