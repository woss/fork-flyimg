<?php

namespace Tests\Core\Processor;

use Tests\Core\BaseTest;

/**
 * Class FaceDetectProcessorTest
 * @package Tests\Core\Processor
 */
class FaceDetectProcessorTest extends BaseTest
{
    /**
     * @throws \Exception
     */
    public function testProcessFaceCropping()
    {
        $image = $this->imageHandler->processImage('fc_1,o_jpg,rf_1', parent::FACES_TEST_IMAGE);
        echo "\n -> ".$image->getOutputImagePath();
        echo "\n -> ".parent::FACES_CP0_TEST_IMAGE;
        $path_parts = pathinfo($image->getOutputImagePath());
        $path_parts2 = pathinfo(parent::FACES_CP0_TEST_IMAGE);
        var_dump($path_parts);
        var_dump($path_parts2);
        $image1Crc32 = crc32(\file_get_contents($image->getOutputImagePath()));
        $image2Crc32 = crc32(\file_get_contents(parent::FACES_CP0_TEST_IMAGE));
        $this->generatedImage[] = $image;
        $this->assertFileExists($image->getOutputImagePath());
        $this->assertEquals($image1Crc32, $image2Crc32);
    }

    /**
     * @throws \Exception
     */
    public function testProcessFaceBlurring()
    {
        $image = $this->imageHandler->processImage('fb_1,o_jpg,rf_1', parent::FACES_TEST_IMAGE);
        $image1Crc32 = crc32(\file_get_contents($image->getOutputImagePath()));
        $image2Crc32 = crc32(\file_get_contents(parent::FACES_BLUR_TEST_IMAGE));
        $this->generatedImage[] = $image;
        $this->assertFileExists($image->getOutputImagePath());
        $this->assertEquals($image1Crc32, $image2Crc32);
    }
}
