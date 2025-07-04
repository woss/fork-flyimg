<?php

namespace Tests\Core;

use Core\Entity\Image\OutputImage;
use Core\Entity\Response;
use Core\Handler\ImageHandler;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

class BaseTest extends TestCase
{
    public const JPG_TEST_IMAGE = __DIR__ . '/../testImages/square.jpg';
    public const PNG_TEST_IMAGE = __DIR__ . '/../testImages/square.png';
    public const WEBP_TEST_IMAGE = __DIR__ . '/../testImages/square.webp';
    public const AVIF_TEST_IMAGE = __DIR__ . '/../testImages/fox.avif';
    public const GIF_TEST_IMAGE = __DIR__ . '/../testImages/animated.gif';

    public const MOVIE_TEST_FILE = __DIR__ . '/../testImages/SampleVideo_1280x720_2mb.mp4';

    public const PDF_TEST_FILE = __DIR__ . '/../testImages/lighthouses.pdf';

    public const FACES_TEST_IMAGE = __DIR__ . '/../testImages/faces.jpg';
    public const FACES_CP0_TEST_IMAGE = __DIR__ . '/../testImages/face_cp0.png';
    public const FACES_CP0_TEST_IMAGE_AVIF = __DIR__ . '/../testImages/face_cp0.avif';
    public const FACES_BLUR_TEST_IMAGE = __DIR__ . '/../testImages/face_fb.png';

    public const EXTRACT_TEST_IMAGE = __DIR__ . '/../testImages/extract-original.jpg';
    public const EXTRACT_TEST_IMAGE_RESULT = __DIR__ . '/../testImages/extract-result.jpg';

    public const SMART_CROP_TEST_IMAGE = __DIR__ . '/../testImages/smart_crop.jpg';
    public const SMART_CROP_TEST_IMAGE_RESULT = __DIR__ . '/../testImages/smart_crop_restult.jpg';

    public const REMOTE_IMAGE_WITH_ARGS_1 = 'https://images.citybreakcdn.com/image.aspx?ImageId=8175306';
    public const REMOTE_IMAGE_WITH_ARGS_2 = 'https://images.citybreakcdn.com/image.aspx?ImageId=7697905';

    public const OPTION_URL = 'w_200,h_100,c_1,bg_#999999,rz_1,sc_50,r_-45,unsh_0.25x0.25+8+0.065,ett_100x80,fb_1,rf_1';
    public const CROP_OPTION_URL = 'w_200,h_100,c_1,rf_1';
    public const GIF_OPTION_URL = 'w_100,h_100,rf_1,o_gif';

    /**
     * @var Application
     */
    protected $app = null;

    /**
     * @var ImageHandler
     */
    protected $imageHandler = null;

    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @var array
     */
    protected $generatedImage = [];

    /**
     *
     */
    public function setUp(): void
    {
        $this->app = $this->createApplication();
        $this->imageHandler = $this->app['image.handler'];
        $this->response = new Response(
            $this->app['image.handler'],
            $this->app['flysystems'],
            $this->app['params']
        );
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        unset($this->imageHandler);
        unset($this->app);

        foreach ($this->generatedImage as $image) {
            if ($image instanceof OutputImage) {
                if (file_exists(UPLOAD_DIR . $image->getOutputImageName())) {
                    unlink(UPLOAD_DIR . $image->getOutputImageName());
                }
                if (file_exists($image->getInputImage()->sourceImagePath())) {
                    unlink($image->getInputImage()->sourceImagePath());
                }
            }
        }
    }


    /**
     * @return Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../app.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }

    /**
     */
    public function testApplicationInstance()
    {
        $this->assertInstanceOf('Silex\Application', $this->app);
    }

    /**
     * @param array $server
     * @return HttpKernelBrowser
     */
    public function createClient(array $server = []): HttpKernelBrowser
    {
        return new HttpKernelBrowser($this->app, $server);
    }
}
