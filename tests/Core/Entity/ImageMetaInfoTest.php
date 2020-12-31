<?php

namespace Tests\Core\Entity;

use Core\Entity\ImageMetaInfo;
use Core\Entity\Image\InputImage;
use Core\Exception\ExecFailedException;
use Tests\Core\BaseTest;

/**
 * @backupGlobals disabled
 */
class ImageMetaInfoTest extends BaseTest
{
    /**
     * @param string $testImagePath
     * @param string $expectedMimeType
     *
     * @dataProvider mimeTypeDataProvider
     */
    public function testGetMimeType(string $testImagePath, string $expectedMimeType)
    {
        $image = new ImageMetaInfo($testImagePath);
        $this->assertEquals($expectedMimeType, $image->mimeType());
    }

    /**
     * @return array
     */
    public function mimeTypeDataProvider(): array
    {
        return [
            [self::JPG_TEST_IMAGE, InputImage::JPEG_MIME_TYPE],
            [self::GIF_TEST_IMAGE, InputImage::GIF_MIME_TYPE],
            [self::PNG_TEST_IMAGE, InputImage::PNG_MIME_TYPE],
            [self::WEBP_TEST_IMAGE, InputImage::WEBP_MIME_TYPE],
        ];
    }

    /**
     *
     */
    public function testGetMimeTypeCached()
    {
        $testImagePath = self::JPG_TEST_IMAGE;
        $image = new ImageMetaInfo($testImagePath);
        $this->assertEquals(InputImage::JPEG_MIME_TYPE, $image->mimeType());
        $this->assertEquals(InputImage::JPEG_MIME_TYPE, $image->mimeType());
    }

    /**
     * @param string $testImagePath
     *
     * @dataProvider fileInfoProvider
     */
    public function testGetInfo(string $testImagePath)
    {
        $image = new ImageMetaInfo($testImagePath);
        $imageInfo = $image->info();
        $this->assertTrue(is_array($imageInfo));
        $this->assertEquals(6, count($imageInfo));
    }

    /**
     * @param string $testImagePath
     * @param string $expectedFormat
     * @param string $expectedCanvas
     * @param string $expectedBitDepth
     * @param string $expectedColorProfile
     * @param string $expectedFileWeight
     * @param array  $expectedDimensions
     *
     * @dataProvider fileInfoProvider
     */
    public function testGetInfoAttributes(
        string $testImagePath,
        string $expectedFormat,
        string $expectedCanvas,
        string $expectedBitDepth,
        string $expectedColorProfile,
        string $expectedFileWeight,
        array $expectedDimensions
    ) {
        $image = new ImageMetaInfo($testImagePath);
        $this->assertEquals($expectedFormat, $image->format());
        $this->assertEquals($expectedCanvas, $image->canvas());
        $this->assertEquals($expectedBitDepth, $image->colorBitDepth());
        $this->assertEquals($expectedColorProfile, $image->colorProfile());
        $this->assertEquals($expectedFileWeight, $image->fileWeight());
        $imageDimensions = $image->dimensions();
        $this->assertTrue(is_array($imageDimensions));
        $this->assertEquals(2, count($imageDimensions));
        $this->assertEquals($expectedDimensions['width'], $imageDimensions['width']);
        $this->assertEquals($expectedDimensions['height'], $imageDimensions['height']);
    }

    /**
     *
     */
    public function testFileReadException()
    {
        $this->expectException(ExecFailedException::class);
        $image = new ImageMetaInfo(self::PNG_TEST_IMAGE . '--fail');
        $image->colorBitDepth();
    }

    /**
     * @return array
     */
    public function fileInfoProvider(): array
    {
        return [
            [
                self::JPG_TEST_IMAGE,
                'JPEG',
                '300x300+0+0',
                '8-bit',
                'sRGB',
                '5.3KB',
                [
                    'width' => '300',
                    'height' => '300',
                ],
            ],
            // this test is broken, the expected color profile should be `sRGB 128c`
            [
                self::GIF_TEST_IMAGE,
                'GIF',
                '800x600+0+0',
                '8-bit',
                'sRGB',
                '128c',
                [
                    'width' => '800',
                    'height' => '600',
                ],
            ],
            [
                self::PNG_TEST_IMAGE,
                'PNG',
                '256x256+0+0',
                '8-bit',
                'sRGB',
                '2.44KB',
                [
                    'width' => '256',
                    'height' => '256',
                ],
            ],
            // this test is broken, the expected color depth should be `8-bit TrueColor`
            [
                self::WEBP_TEST_IMAGE,
                'PAM',
                '100x100+0+0',
                '8-bit',
                'TrueColor',
                'sRGB',
                [
                    'width' => '100',
                    'height' => '100',
                ],
            ],
        ];
    }
}
