<?php

namespace Tests\Core\Exception;

use Core\Exception\FileNotFoundException;

class FileNotFoundExceptionTest extends ExceptionBaseTest
{
    public function testFileNotFoundException()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File not found');

        $options = 'w_100,h_100,rf_1';
        $sourceImage = 'http://localhost:8089/?code=404';
        $image = $this->imageHandler->processImage($options, $sourceImage);
    }
}
