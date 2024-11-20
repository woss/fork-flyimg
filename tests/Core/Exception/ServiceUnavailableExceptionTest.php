<?php

namespace Tests\Core\Exception;

use Core\Exception\ServiceUnavailableException;

class ServiceUnavailableExceptionTest extends ExceptionBaseTest
{
    public function testServiceUnavailableException()
    {
        $this->expectException(ServiceUnavailableException::class);
        $this->expectExceptionMessage('The server is temporarily unable to process requests');

        $options = 'w_100,h_100,rf_1';
        $sourceImage = 'http://localhost:8089/?code=503';
        $image = $this->imageHandler->processImage($options, $sourceImage);
    }
}
