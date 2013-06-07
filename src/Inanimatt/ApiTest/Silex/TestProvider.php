<?php
namespace Inanimatt\ApiTest\Silex;

use Inanimatt\ApiTest\Controller\TestController;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

class TestProvider implements ControllerProviderInterface, ServiceProviderInterface
{

    // Register services: namely the controller and any dependencies it requires
    public function register(Application $app)
    {
        $app['test.controller'] = $app->share(function () use ($app) {
            return new TestController($app['oauth2.resource_server'], $app['api.response']);
        });
    }

    public function boot(Application $app) {}

    // Create routes to all the controller's methods
    public function connect(Application $app)
    {
        $test = $app['controllers_factory'];

        $test->get('/test-array', "test.controller:testArray")->before($app['oauth2.check_token']);
        $test->get('/test-response', "test.controller:testResponse")->before($app['oauth2.check_token']);

        return $test;
    }
}
