<?php

namespace Tests\Core\Controller;

use Core\Exception\ExecFailedException;
use Core\Exception\InvalidArgumentException;
use Core\Exception\ReadFileException;
use Silex\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Core\BaseTest;

class DefaultControllerTest extends WebTestCase
{
    /**
     *
     */
    protected function tearDown()
    {
        unset($this->app);
    }

    /**
     *
     */
    public function testIndexAction()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
    }

    /**
     *
     */
    public function testUploadAction()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,c_1,rf_1,o_png/'.BaseTest::JPG_TEST_IMAGE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadActionWebp()
    {
        $client = static::createClient();
        $client->request('GET', 'upload/o_webp/'.BaseTest::PNG_TEST_IMAGE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadActionGif()
    {
        $client = static::createClient();
        $client->request('GET', 'upload/o_gif/'.BaseTest::PNG_TEST_IMAGE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadActionWithFaceDetection()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/fc_1/'.BaseTest::FACES_TEST_IMAGE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadActionForbidden()
    {
        $this->expectException(ReadFileException::class);
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,c_1/Rovinj-Croatia-nonExist.jpg');
    }

    /**
     *
     */
    public function testUploadActionInvalidExtension()
    {
        $this->expectException(InvalidArgumentException::class);
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,c_1,o_xxx/'.BaseTest::JPG_TEST_IMAGE);
    }

    /**
     *
     */
    public function testPathAction()
    {
        $client = static::createClient();
        $client->request('GET', '/path/w_200,h_200,c_1/'.BaseTest::JPG_TEST_IMAGE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testPathActionForbidden()
    {
        $this->expectException(ReadFileException::class);
        $client = static::createClient();
        $client->request('GET', '/path/w_200,h_200,c_1/Rovinj-Croatia-nonExist.jpg');
    }

    /**
     *
     */
    public function testUploadPdfNoPageSpecified()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200/'.BaseTest::PDF_TEST_FILE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadPdfPage2()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,pg_2/'.BaseTest::PDF_TEST_FILE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadMovieNoTimeSpecified()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200/'.BaseTest::MOVIE_TEST_FILE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadMovie5Seconds()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,tm_5/'.BaseTest::MOVIE_TEST_FILE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadMovie10Seconds()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,tm_00:00:10/'.BaseTest::MOVIE_TEST_FILE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadMovie10SecondsRefresh()
    {
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,tm_00:00:10,rf_1/'.BaseTest::MOVIE_TEST_FILE);
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertFalse($client->getResponse()->isEmpty());
    }

    /**
     *
     */
    public function testUploadMovie20SecondsFails()
    {
        $this->expectException(ExecFailedException::class);
        $client = static::createClient();
        $client->request('GET', '/upload/w_200,h_200,tm_00:00:20/'.BaseTest::MOVIE_TEST_FILE);
    }

    /**
     * Creates the application.
     *
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../app.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }
}
