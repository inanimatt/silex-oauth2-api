<?php
require __DIR__.'/../vendor/autoload.php';

// TODO: switch to Symfony 2.3 DI component, lazy loading, cached container
$di = new \Pimple;

// Initiate the Request handler
$di['oauth_request'] = $di->share(function () {
    return new \OAuth2\Util\Request();
});

// Initiate a new database connection
$di['db'] = $di->share(function () {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
        'dbname'   => 'hubapi_slim',
        'user'     => 'root',
        'password' => '',
        'host'     => 'localhost',
        'driver'   => 'pdo_mysql',
        'encoding' => 'utf8',
    );
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    
    return $conn;
});

// Initiate the auth server with the models
$di['oauth_server'] = $di->share(function () use ($di) {
    return new League\OAuth2\Server\Resource(
        new Inanimatt\OAuth2\Server\Storage\DBAL\Session($di['db'])
    );
});

$di['check_token'] = $di->share(function ($di) {

    return function(\Slim\Route $route) use ($di)
    {
        $server = $di['oauth_server'];

        // Test for token existance and validity
        try {
            $server->isValid();
        }

        // The access token is missing or invalid...
        catch (League\OAuth2\Server\Exception\InvalidAccessTokenException $e)
        {
            $app = \Slim\Slim::getInstance();
            $res = $app->response();
            $res['Content-Type'] = 'application/json';
            $res->status(403);

            $res->body(json_encode(array(
                'error' =>  $e->getMessage()
            )));
            $app->stop();
        }
    };

});

$app = new Slim\Slim(array(
    'debug' => true,
));


$app->get('/', $di['check_token'], function () use ($app, $di) {
    $result = array(
        'result' => 'Hello world',
    );

    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->body(json_encode($result));
});

return $app;