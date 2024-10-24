<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Routing\RouteCollection;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Registry;
use Silex\Application;

/**
 * Define constants
 */
define('ROOT_DIR', __DIR__);
define('UPLOAD_WEB_DIR', 'uploads/');
define('UPLOAD_DIR', __DIR__ . '/web/' . UPLOAD_WEB_DIR);
define('TMP_DIR', __DIR__ . '/var/tmp/');

/**
 * Create directories if they don't exist
 */
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
if (!is_dir(TMP_DIR)) {
    mkdir(TMP_DIR, 0777, true);
    }

/**
 * Create Silex application
 */
$app = new Application();

/**
 * Load application parameters
 */
$app['params'] = new \Core\Entity\AppParameters(__DIR__ . '/config/parameters.yml');

/**
 * Logging via Monolog settings
 */
$logLevel = $app['params']->parameterByKey('log_level');
$logger = new Logger('flyimg');
$logger->pushHandler(new StreamHandler('php://stdout', $logLevel));
Registry::addLogger($logger);
$app['logger'] = $logger;

$exceptionHandlerFunction = function (\Exception $e) use ($app): void {
    $request = $app['request_stack']->getCurrentRequest();
    $app['logger']->error(
        '',
        [
            'error' => $e->getMessage(),
            'uri' => is_null($request) ? '' : $request->getUri(),
            'user_agent' => is_null($request) ? '' : $request->headers->get('User-Agent'),
            'file' => $e->getFile() . ':' . $e->getLine()
        ]
    );
};

if (!isset($_ENV['env']) || $_ENV['env'] !== 'test') {
    $app->error($exceptionHandlerFunction);
}

/**
 * Register error handler
 */
ErrorHandler::register();
$exceptionHandler = ExceptionHandler::register($app['params']->parameterByKey('debug'));
$exceptionHandler->setHandler($exceptionHandlerFunction);

/**
 * Routes
 */
$routesResolver = new \Core\Resolver\RoutesResolver();
$app['routes'] = $app->extend(
    'routes',
    function (RouteCollection $routes) use ($routesResolver) {
        return $routesResolver->parseRoutesFromYamlFile($routes, __DIR__ . '/config/routes.yml');
    }
);

/**
 * Register Storage provider
 */
switch ($app['params']->parameterByKey('storage_system')) {
    case 's3':
        $app->register(new \Core\StorageProvider\S3StorageProvider());
        break;
    case 'local':
    default:
        $app->register(new \Core\StorageProvider\LocalStorageProvider());
        break;
}

/**
 * Controller Resolver
 *
 * @param \Silex\Application $app
 *
 * @return \Core\Resolver\ControllerResolver
 */
$app['resolver'] = function (\Silex\Application $app) {
    return new \Core\Resolver\ControllerResolver($app, $app['logger']);
};

/**
 * Register Image Handler
 *
 * @param \Silex\Application $app
 *
 * @return \Core\Handler\ImageHandler
 */
$app['image.handler'] = function (\Silex\Application $app): \Core\Handler\ImageHandler {
    return new \Core\Handler\ImageHandler(
        $app['flysystems']['storage_handler'],
        $app['params']
    );
};

/**
 * To generate a hashed url when security key is enabled
 * Example usage: php app.php encrypt w_200,h_200,c_1/Rovinj-Croatia.jpg
 */
if (!empty($argv[1]) && !empty($argv[2]) && $argv[1] == 'encrypt') {
    printf("Hashed request: %s\n", $app['image.handler']->securityHandler()->encrypt($argv[2]));
    return;
}

/**
 * Debug configuration
 */
$app['debug'] = $app['params']->parameterByKey('debug');

return $app;
