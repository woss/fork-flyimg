<?php

namespace Tests\Core\Exception;

use Core\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends ExceptionBaseTest
{
    public function testInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument');

        $options = 'w_100,h_100,rf_1';
        $sourceImage = 'http://localhost:8089/?code=400';
        $image = $this->imageHandler->processImage($options, $sourceImage);
    }
}
