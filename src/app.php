<?php
require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application;
$app['debug'] = true;

// Initiate the Request handler
$app['oauth_request'] = $app->share(function () {
    return Inanimatt\OAuth2\Server\Util\Request::buildFromGlobals();
});

$app['json_response'] = function () {
    return new \Symfony\Component\HttpFoundation\JsonResponse;
};

// Initiate a new database connection
$app['db'] = $app->share(function () {
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
$app['oauth_server'] = $app->share(function () use ($app) {
    return new League\OAuth2\Server\Resource(
        new Inanimatt\OAuth2\Server\Storage\DBAL\Session($app['db'])
    );
});

$app['auth_server'] = $app->share(function () use ($app) {
    $server = new League\OAuth2\Server\Authorization(
        new Inanimatt\OAuth2\Server\Storage\DBAL\Client($app['db']),
        new Inanimatt\OAuth2\Server\Storage\DBAL\Session($app['db']),
        new Inanimatt\OAuth2\Server\Storage\DBAL\Scope($app['db'])
    );

    $server->addGrantType(new League\OAuth2\Server\Grant\ClientCredentials($server));
    $server->setRequest($app['oauth_request']);

    return $server;
});

$app['check_token'] = $app->share(function () use ($app) {

    return function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
        $server = $app['oauth_server'];

        // Test for token existance and validity
        try {
            $server->isValid();
        }

        // The access token is missing or invalid...
        catch (League\OAuth2\Server\Exception\InvalidAccessTokenException $e)
        {
            $response = $app['json_response'];
            $response->setData(array(
                'error' => $e->getMessage(),
            ));
            $response->setStatusCode(403);

            return $response;
        }
    };

});

$app->post('/oauth/v2/token', function () use ($app) {

    $result = $app['auth_server']->issueAccessToken();

    $response = $app['json_response'];
    $response->setData($result);

    return $response;
});

$app->get('/api/assets', function () use ($app) {
    $result = array(
        'result' => 'Hello world',
    );

    $response = $app['json_response'];
    $response->setData($result);

    return $response;
})->before($app['check_token']);

return $app;
