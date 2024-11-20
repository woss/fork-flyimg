<?php

namespace Tests\Core\Exception;

use Core\Exception\UnauthorizedException;

class UnauthorizedExceptionTest extends ExceptionBaseTest
{
    public function testUnauthorizedException()
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage(
            "The server cannot process the request \
        due to lacks valid authentication credentials for the requested resource."
        );

        $options = 'w_100,h_100,rf_1';
        $sourceImage = 'http://localhost:8089/?code=401';
        $image = $this->imageHandler->processImage($options, $sourceImage);
    }
}
