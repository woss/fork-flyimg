<?php

namespace Core\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExecFailedException extends HttpException
{
  public function __construct($message) {

    parent::__construct(500, $message);
  }
}
