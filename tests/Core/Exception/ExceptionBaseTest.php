<?php

namespace Tests\Core\Exception;

use Tests\Core\BaseTest;
use Symfony\Component\Process\Process;

class ExceptionBaseTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        $process = new Process(['php', '-S', 'localhost:8089', 'tests/Core/MockResponseCodeServer.php']);
        $process->start();
        usleep(10000); 
        parent::setUpBeforeClass();
    }
}
