<?php

namespace Tests\Core\Processor;

use Tests\Core\BaseTest;

/**
 * Class ExtractProcessorTest
 */
class ExtractProcessorTest extends BaseTest
{
    /**
     * @throws \Exception
     */
    public function testExecuteSuccess()
    {
        $image = $this->imageHandler->processImage(
            'e_1,p1x_100,p1y_100,p2x_300,p2y_300,o_jpg,rf_1',
            parent::EXTRACT_TEST_IMAGE
        );
        $image1Crc32 = crc32(\file_get_contents($image->getOutputImagePath()));
        $image2Crc32 = crc32(\file_get_contents(parent::EXTRACT_TEST_IMAGE_RESULT));
        $this->generatedImage[] = $image;
        $this->assertFileExists($image->getOutputImagePath());
        $this->assertEquals($image1Crc32, $image2Crc32);
    }
}
