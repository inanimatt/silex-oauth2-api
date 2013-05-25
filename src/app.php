<?php
require __DIR__.'/../vendor/autoload.php';

$di = new \Pimple;

// Initiate the Request handler
$di['oauth_request'] = $di->share(function () {
    return new \OAuth2\Util\Request();
});

// Initiate a new database connection
$di['oauth_db'] = $di->share(function () {
    return new League\OAuth2\Server\Storage\PDO\Db('mysql://user:pass@localhost/oauth');
});

// Initiate the auth server with the models
$di['oauth_server'] = $di->share(function () use ($di) {
    return new League\OAuth2\Server\Resource(
        new League\OAuth2\Server\Storage\PDO\Session($di['oauth_db'])
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