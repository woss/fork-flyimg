<?php

namespace Core\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppException extends HttpException
{
  public function __construct() {
    parent::__construct( 500, "Internal Server Error");
  }
}
