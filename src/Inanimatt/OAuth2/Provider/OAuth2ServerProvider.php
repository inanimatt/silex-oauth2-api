<?php
namespace Inanimatt\OAuth2\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class OAuth2ServerProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    public function register(Application $app)
    {

        // Request and response classes
        $app['oauth2.request'] = $app->share(function () {
            return \Inanimatt\OAuth2\Server\Util\Request::buildFromGlobals();
        });

        // Resource server (the API)
        $app['oauth2.resource_server'] = $app->share(function () use ($app) {
            return new \League\OAuth2\Server\Resource(
                new \Inanimatt\OAuth2\Server\Storage\DBAL\Session($app['db'])
            );
        });

        /**
         * Authorization server (hands out tokens after authentication, grants
         * access, authenticates client_credentials grants)
         */
        $app['oauth2.authorization_server'] = $app->share(function () use ($app) {
            $server = new \League\OAuth2\Server\Authorization(
                new \Inanimatt\OAuth2\Server\Storage\DBAL\Client($app['db']),
                new \Inanimatt\OAuth2\Server\Storage\DBAL\Session($app['db']),
                new \Inanimatt\OAuth2\Server\Storage\DBAL\Scope($app['db'])
            );

            $server->addGrantType(new \League\OAuth2\Server\Grant\ClientCredentials($server));
            $server->setRequest($app['oauth2.request']);

            return $server;
        });

        // Register authorisation route middleware
        $app['oauth2.check_token'] = $app->protect(function (Request $request) use ($app) {
            $server = $app['oauth2.resource_server'];

            // Test for token existance and validity
            try {
                $server->isValid();
            }

            // The access token is missing or invalid...
            catch (\League\OAuth2\Server\Exception\InvalidAccessTokenException $e)
            {
                $error = array(
                    'error' => $e->getMessage(),
                );

                return $app->json($error, 403);
            }
        });
    }

    public function boot(Application $app) {}

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->post('/oauth/v2/token', function () use ($app) {

            $result = $app['oauth2.authorization_server']->issueAccessToken();

            return $app->json($result);
        });

        return $controllers;
    }

}