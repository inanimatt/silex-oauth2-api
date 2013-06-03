<?php
namespace Inanimatt\ApiTest\Controller;

use League\OAuth2\Server\Resource;

class TestController
{
    protected $resource_server;

    // Just an example dependency.
    public function __construct(Resource $resource_server)
    {
        $this->resource_server = $resource_server;
    }

    // Simple hello world example
    public function test()
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
}
