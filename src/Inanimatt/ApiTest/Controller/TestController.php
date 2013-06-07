<?php
namespace Inanimatt\ApiTest\Controller;

use League\OAuth2\Server\Resource;
use Inanimatt\Api\ApiResponse;

class TestController
{
    protected $resource_server;
    protected $response_prototype;

    // Just an example dependency.
    public function __construct(Resource $resource_server, ApiResponse $response)
    {
        $this->resource_server = $resource_server;
        $this->response_prototype = $response;
    }

    // Simple hello world example using the array-to-response transformer
    public function testArray()
    {
        $result = array(
            'httpStatus' => 200,
            'httpHeaders' => array(
                'Link' => '<https://example.com/api-docs/test.html>; rel="help"',
            ),
            'result' => 'Hello world',
            'client_id' => $this->resource_server->getClientId(),
            'scopes' => $this->resource_server->getScopes(),
        );

        return $result;
    }

    // Simple hello world example, using ApiResponse object
    public function testResponse()
    {
        $result = array(
            'result' => 'Hello world',
            'client_id' => $this->resource_server->getClientId(),
            'scopes' => $this->resource_server->getScopes(),
        );

        $response = clone $this->response_prototype;
        $response->setDeprecated(true);
        $response->setDocumentation('/api-docs/test.html');
        $response->setData($result);

        return $response;
    }
}
