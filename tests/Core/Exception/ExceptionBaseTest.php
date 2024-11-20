<?php

namespace Tests\Core\Exception;

use Tests\Core\BaseTest;
use Symfony\Component\Process\Process;

class ExceptionBaseTest extends BaseTest
{
     /** @var Process */
     private static $process;

    public static function setUpBeforeClass(): void
    {
        self::$process = new Process(['php', '-S', 'localhost:8089', 'tests/MockResponseCodeServer.php']);
        self::$process->start();
        usleep(10000);
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        self::$process->stop();
    }
}
