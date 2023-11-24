<?php

namespace Tests\Core\Processor;

use Tests\Core\BaseTest;

/**
 * Class SmartCropProcessorTest
 * @package Tests\Core\Processor
 */
class SmartCropProcessorTest extends BaseTest
{
    /**
     * @throws \Exception
     */
    public function testSmartCrop()
    {
        $image = $this->imageHandler->processImage('smc_1,rf_1,o_jpg', parent::SMART_CROP_TEST_IMAGE);
        $filesize = filesize($image->getOutputTmpPath());
        $filesize2 = filesize(parent::EMART_CROP_TEST_IMAGE_RESULT);
        $this->generatedImage[] = $image;
        $this->assertFileExists($image->getOutputTmpPath());
        $this->assertEquals($filesize, $filesize2);
    }
}
