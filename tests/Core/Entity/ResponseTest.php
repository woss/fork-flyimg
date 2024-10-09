<?php

namespace Core\Entity;

use Tests\Core\BaseTest;

class ResponseTest extends BaseTest
{
    /**
     * @return void
     * @throws \Exception
     */
    public function testEnableCacheAndGenerateImage(): void
    {
        $this->app['params']->addParameter('disable_cache', false);
        $image = $this->imageHandler->processImage('w_10,h_10,rf_1', parent::PNG_TEST_IMAGE);
        $this->generatedImage[] = $image;
        $this->assertTrue($this->app['flysystems']['storage_handler']->has($image->getOutputImageName()));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testDisableCacheAndGenerateImage(): void
    {
        $this->app['params']->addParameter('disable_cache', true);
        $image = $this->imageHandler->processImage('w_100,h_250,rf_1', parent::JPG_TEST_IMAGE);
        $this->response->generateImageResponse($image);
        $this->assertFalse($this->app['flysystems']['storage_handler']->has($image->getOutputImageName()));
    }

    /**
     * @return void
     */
    public function testImageSameUrlDifferentArgs(): void
    {
        $image1 = $this->imageHandler->processImage('w_100,h_250,rf_1', parent::REMOTE_IMAGE_WITH_ARGS_1);
        $image2 = $this->imageHandler->processImage('w_100,h_250,rf_1', parent::REMOTE_IMAGE_WITH_ARGS_2);
        $this->response->generateImageResponse($image1);
        $this->response->generateImageResponse($image2);
        $this->assertNotEquals(
            $this->app['flysystems']['storage_handler']->checksum($image1->getOutputImageName()), 
            $this->app['flysystems']['storage_handler']->checksum($image2->getOutputImageName())
        );
    }
}
