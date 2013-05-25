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
        'dbname'   => 'mydb',
        'user'     => 'myusername',
        'password' => 'lovesexsecret',
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


$app = new Slim\Slim(array(
    'debug' => true,
));

$app->get('/', function () use ($app, $di) {
    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->body(json_encode(array(
        'result' => 'Hello world',
    )));
});

return $app;