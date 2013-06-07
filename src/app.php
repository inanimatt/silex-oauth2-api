<?php
require __DIR__.'/../vendor/autoload.php';

use Inanimatt\Silex\ArrayToJsonListener;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application;
$app['debug'] = false;

// Require SSL/TLS. See the README for why you shouldn't change this
$app['controllers']->requireHttps();

// Set up some handy API services
$app->register(new Inanimatt\Silex\ApiServiceProvider('1.0.0-alpha')); // Your API version string here

// Initiate a new database connection
$app['db'] = $app->share(function () {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = require __DIR__.'/database.php';

    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

    return $conn;
});


// Register OAuth2 server and controllers
$oauth2_service_provider = new Inanimatt\OAuth2\Provider\OAuth2ServerProvider();
$app->register($oauth2_service_provider);
$app->mount('/', $oauth2_service_provider);

/* Define API routes
 * This is the fancy way with controllers as services. Just comment this code
 * out and replace it with your own routes. I recommend this method, however 
 * for maintainability and portability. See: 
 * http://silex.sensiolabs.org/doc/providers/service_controller.html
 */
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$test_provider = new Inanimatt\ApiTest\Silex\TestProvider();
$app->register($test_provider);
$app->mount('/api/v1', $test_provider);

return $app;
