<?php
namespace Inanimatt\Silex;

use Inanimatt\Api\ApiResponse;
use Inanimatt\Silex\ArrayToJsonListener;
use Silex\ServiceProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiServiceProvider implements ServiceProviderInterface
{
    protected $version;

    public function __construct($version)
    {
        $this->version = $version;
    }

    public function register(Application $app)
    {
        $app['api.version'] = $this->version;

        // Require that clients accept JSON (or */*)
        $app->before(function (Request $request) {
            $formats = $request->getAcceptableContentTypes();

            if (!(
                in_array('application/json', $formats) // Supported
                || in_array('*/*', $formats) // Hey, you asked for it, buddy
            )){
                $message = '<html><head><title>Error</title></head><body><h1>Error</h1><p>This API currently only supports JSON
                responses, and the headers of your HTTP request don\'t include the <code>application/json</code> format in the
                <code>Accept</code> header,</p></body></html>';

                return new Response($message, 406);
            }
        });

        // Convert JSON request bodies into request parameters
        $app->before(function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        // Prepare API Response object
        $app['api.response'] = function () use ($app) {
            $response = new ApiResponse();
            $response->setVersion($app['api.version']);

            return $response;
        };

        // JSON Exception handling
        $app->error(function (\Exception $e, $code) use ($app) {
            if ($app['debug']) {
                return;
            }

            $format = $app['request']->attributes->get('format');

            $code = $e->getCode() ?: $code;
            if ($code < 100 || $code > 600) {
                $code = 500;
            }

            $response = $app['api.response'];
            $response->setData(array(
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ));
            $response->headers->set('X-Status-Code', $code);

            return $response;
        });
    }

    public function boot(Application $app)
    {
        // Convert array responses to JSON
        $app['dispatcher']->addSubscriber(new ArrayToJsonListener($app['api.response']));
    }
}
