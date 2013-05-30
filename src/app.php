<?php
require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application;
$app['debug'] = false;

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

// Define API routes
$app->get('/api/test', function () use ($app) {
    $result = array(
        'result' => 'Hello world',
        'client_id' => $app['oauth2.resource_server']->getClientId(),
        'scopes' => $app['oauth2.resource_server']->getScopes(),
    );

    return $app->json($result);
})->before($app['oauth2.check_token']);

return $app;
