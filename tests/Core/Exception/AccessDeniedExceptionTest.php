<?php

namespace Tests\Core\Exception;

use Core\Exception\AccessDeniedException;

class AccessDeniedExceptionTest extends ExceptionBaseTest
{
    /**
     * @throws AccessDeniedException
     */
    public function testAccessDeniedException()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');

        $options = 'w_100,h_100,rf_1';
        $sourceImage = 'http://localhost:8089/?code=403';
        $image = $this->imageHandler->processImage($options, $sourceImage);
    }
}
