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

// Initiate a new database connection
$app['db'] = $app->share(function () {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = require __DIR__.'/database.php';

    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

    return $conn;
});

// Convert array responses to JSON - the argument is the API version string you want to send
$app['dispatcher']->addSubscriber(new ArrayToJsonListener('1.0.0-alpha'));

// Require that clients accept JSON (or */*)
$app->before(function (Request $request) {
    $formats = $request->getAcceptableContentTypes();

    if (!(
        in_array('application/json', $formats) // Supported
        || in_array('*/*', $formats) // Hey, you asked for it, buddy
    )){
        $message = '<html><head><title>Error</title></head><body><h1>Error</h1><p>This API currently only supports JSON
        responses, and the headers of your HTTP request don\'t include the <code>application/json</code> format in the
        <code>Accept</code> header.</p></body></html>';

        return new Response($message, 406);
    }
});

// JSON Exception handling
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $format = $app['request']->attributes->get('format');

    $code = $e->getCode() ?: $code;

    $response = new JsonResponse();
    $response->setData(array(
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
    ));
    $response->headers->set('X-Status-Code', $code);

    return $response;
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
