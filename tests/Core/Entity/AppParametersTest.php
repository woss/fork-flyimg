<?php

namespace Tests\Core\Entity;

use Core\Entity\AppParameters;
use Core\Exception\AppException;
use PHPUnit\Framework\TestCase;

class AppParametersTest extends TestCase
{
    /**
     * Test SaveToTemporaryFileException
     */
    public function testParamFileNotExist()
    {
        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Parameter file not found at : not_existing_file.yml');
        new AppParameters('not_existing_file.yml');
    }
}
