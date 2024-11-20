<?php

namespace Tests\Core\Exception;

use Core\Exception\AppException;

class AppExceptionTest extends ExceptionBaseTest
{
    public function testAppException()
    {
        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Internal server error');

        $options = 'w_100,h_100,rf_1';
        $sourceImage = 'http://localhost:8089/?code=500';
        $image = $this->imageHandler->processImage($options, $sourceImage);
    }
}
