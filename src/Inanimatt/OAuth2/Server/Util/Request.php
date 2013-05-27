<?php
namespace Inanimatt\OAuth2\Server\Util;

use Symfony\Component\HttpFoundation\Request as sfRequest;
use League\OAuth2\Server\Util\Request as BaseRequest;
/**
 * A facade to implement LOEP OAuth2 Server's request interface with a Symfony HTTP Foundation Request object
 */

class Request extends BaseRequest
{
    public static function buildFromGlobals() {
        $request = sfRequest::createFromGlobals();

        $get = iterator_to_array($request->query);
        $post = iterator_to_array($request->request);
        $cookies = iterator_to_array($request->cookies);

        // FIXME: not sure this actually works
        $files = iterator_to_array($request->files);

        $server = iterator_to_array($request->server);
        $headers = iterator_to_array($request->headers);

        return new Request($get, $post, $cookies, $files, $server, $headers);
    }

}