<?php

namespace Core\Entity;

use Tests\Core\BaseTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /** Ensure handleUploadStream handles multipart file. */
    public function testHandleUploadStreamMultipartFile(): void
    {
        $file = new UploadedFile(
            parent::PNG_TEST_IMAGE,
            basename(parent::PNG_TEST_IMAGE),
            'image/png',
            null,
            true
        );

        $request = new Request([], [], [], [], ['file' => $file]);
        $this->response->handleUploadStream('w_50,o_jpg', $request);
        $this->assertNotEmpty($this->response->getContent());
        $this->assertStringContainsString('image/', $this->response->headers->get('Content-Type'));
    }

    /** Ensure handleUploadStream handles JSON {base64}. */
    public function testHandleUploadStreamJsonBase64(): void
    {
        $binary = file_get_contents(parent::JPG_TEST_IMAGE);
        $payload = json_encode(['base64' => base64_encode($binary)]);
        $request = Request::create(
            '/upload',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );
        $this->response->handleUploadStream('w_60,o_png', $request);
        $this->assertNotEmpty($this->response->getContent());
    }

    /** Ensure handleUploadStream handles JSON {dataUri}. */
    public function testHandleUploadStreamJsonDataUri(): void
    {
        $binary = file_get_contents(parent::PNG_TEST_IMAGE);
        $dataUri = 'data:image/png;base64,' . base64_encode($binary);
        $payload = json_encode(['dataUri' => $dataUri]);
        $request = Request::create(
            '/upload',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );
        $this->response->handleUploadStream('w_70,o_jpg', $request);
        $this->assertNotEmpty($this->response->getContent());
    }

    /** Ensure handleUploadStream handles octet-stream raw binary. */
    public function testHandleUploadStreamOctetStreamBinary(): void
    {
        $binary = file_get_contents(parent::PNG_TEST_IMAGE);
        $request = Request::create(
            '/upload',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/octet-stream'],
            $binary
        );
        $this->response->handleUploadStream('w_65,o_jpg', $request);
        $this->assertNotEmpty($this->response->getContent());
    }

    /** Ensure handleUploadStream handles octet-stream base64 string (no data URI). */
    public function testHandleUploadStreamOctetStreamBase64(): void
    {
        $binary = file_get_contents(parent::PNG_TEST_IMAGE);
        $base64 = base64_encode($binary);
        $request = Request::create(
            '/upload',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/octet-stream'],
            $base64
        );
        $this->response->handleUploadStream('w_75,o_jpg', $request);
        $this->assertNotEmpty($this->response->getContent());
    }

    /** Ensure handleUploadStream handles octet-stream body containing data URI. */
    public function testHandleUploadStreamOctetStreamDataUri(): void
    {
        $binary = file_get_contents(parent::JPG_TEST_IMAGE);
        $dataUri = 'data:image/jpeg;base64,' . base64_encode($binary);
        $request = Request::create(
            '/upload',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/octet-stream'],
            $dataUri
        );
        $this->response->handleUploadStream('w_85,o_png', $request);
        $this->assertNotEmpty($this->response->getContent());
    }

    /** Ensure handleUploadStream handles text/plain base64 string. */
    public function testHandleUploadStreamTextPlainBase64(): void
    {
        $binary = file_get_contents(parent::PNG_TEST_IMAGE);
        $base64 = base64_encode($binary);
        $request = Request::create(
            '/upload',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            $base64
        );
        $this->response->handleUploadStream('w_95,o_jpg', $request);
        $this->assertNotEmpty($this->response->getContent());
    }

    /** Ensure handleUploadStream handles text/plain data URI string. */
    public function testHandleUploadStreamTextPlainDataUri(): void
    {
        $binary = file_get_contents(parent::PNG_TEST_IMAGE);
        $dataUri = 'data:image/png;base64,' . base64_encode($binary);
        $request = Request::create(
            '/upload',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            $dataUri
        );
        $this->response->handleUploadStream('w_100,o_jpg', $request);
        $this->assertNotEmpty($this->response->getContent());
    }
}
