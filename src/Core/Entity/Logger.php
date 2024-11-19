<?php

namespace Core\Entity;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Registry;

class Logger extends MonologLogger
{
    private $logger;

    public function __construct($name, $logLevel)
    {
        $this->logger = new MonologLogger($name);
        $this->logger->pushHandler(new StreamHandler('php://stdout', $logLevel));
        Registry::addLogger($this->logger);
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
