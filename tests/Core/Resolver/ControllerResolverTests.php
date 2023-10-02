<?php

namespace TestsCore\Resolver;

use Core\Exception\InvalidArgumentException;
use Silex\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tests\Core\BaseTest;

/**
 * Class ControllerResolverTests
 */
class ControllerResolverTests extends BaseTest
{
    /**
     *
     */
    protected function tearDown(): void
    {
        unset($this->app);
    }

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../app.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        $app['routes'] = $app->extend(
            'routes',
            function (RouteCollection $routes, Application $app) {
                $collection = new RouteCollection();

                $route = new Route(
                    '/UndefinedController',
                    ['_controller' => 'Core\Controller\CoreController']
                );
                $collection->add('undefined_controller', $route);

                $collection2 = new RouteCollection();
                $route = new Route(
                    '/UndefinedClass',
                    ['_controller' => 'Core\Controller\NotFoundController::pathAction']
                );
                $collection2->add('undefined_class', $route);

                $routes->addCollection($collection);
                $routes->addCollection($collection2);

                return $routes;
            }
        );

        return $app;
    }

    /**
     */
    public function testClassNotExist()
    {
        $this->expectException(InvalidArgumentException::class);

        $client = static::createClient();
        $client->request('GET', '/UndefinedClass');
    }

    /**
     */
    public function testControllerNotExist()
    {
        $this->expectException(InvalidArgumentException::class);

        $client = static::createClient();
        $client->request('GET', '/UndefinedController');
    }

    /**
     */
    public function testUndefinedRoute()
    {
        $this->expectException(NotFoundHttpException::class);

        $client = static::createClient();
        $client->request('GET', '/UndefinedRoute');
    }
}
