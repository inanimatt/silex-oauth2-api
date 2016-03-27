# Don't use this, it's unmaintained and out of date.

# A minimal working Silex/OAuth 2.0-based API

Stuff it does:

* Actually bloody works. I'm not proud, but I am tired.

* Implements a Doctrine DBAL storage interface for the [LOEP OAuth 2 server](https://github.com/php-loep/oauth2-server). (Because Silex has built-in integration for the DBAL, not because I think it's better or worse than zetacomponents).

* Provides an access token for the `client_credentials` grant type. 

* Defines a route middleware service that checks for a valid access token.

* Basic JSON exception handling

* Example of coding framework-independent controllers as services, registered with a Silex provider.

* Registers a listener that converts array responses to JSON responses so that your framework-independent controllers don't have to depend on the Symfony JsonResponse class.

* Provides an `ApiResponse` object that extends `JsonResponse`, adding API version, documentation and deprecation headers, and pretty-printed output.

Stuff it doesn't do:

* There are no Authorization Server routes (e.g. for creating & authorising clients), except for the token granting route. There's an authorisation server implementation at [fkooman/php-oauth](https://github.com/fkooman/php-oauth) if you're looking for hints on how to do this. 

* Only the `client_credentials` grant type is implemented. Adding the other grant types should be straightforward, though, and `client_credentials` might be just fine depending on what your API does.

* No proper content negotiation; just dies if `application/json` isn't accepted.

* You must define your own scopes (just insert them into the `oauth_scopes` table), or resign yourself to not using them.

## Known issues

* The JSON exception handler makes a hell of an assumption about the exception code: if it's between 100-600, it assumes that it's an HTTP status code. That's fine for exceptions you throw, because you can set the code yourself, but there are all kinds of other things that can go wrong, and in those cases the status code will be a bit of a crap-shoot. You might want to change it so that only the error codes of exceptions you define have their error codes trusted. If I think of a nice way to do this, I'll do it myself, but I'm open to suggestions!

## Using it

* Clone, fork, or download this repository. 

* Install the required dependencies with [Composer](http://getcomposer.org), e.g. `composer install`.

* **Set up an SSL VHost** and point its document root at the `web` folder. **This is important!** OAuth 2.0 requires a TLS connection; it is not secure without it. You should test and develop with an SSL VHost too (you can use a self-signed certificate) so that you know it works. I've put a global requirement on the app to require https; think carefully before removing it.

* A `.htaccess` file is provided for Apache, but you can of course serve with nginx or whatever else you want instead. In fact that's probably better.

* Copy `src\database.php.dist` to `src\database.php` and customise. Or configure your database another way. You don't *have* to use Doctrine DBAL, but if you don't, you'll have to write your own implementation of the `League/OAuth2/Server/Storage` classes and replace the `Inanimatt\OAuth2\Provider\OAuth2ServerProvider` with your own (or just override the `oauth2.resource_server` and `oauth2.authorization_server` services). Not a big deal; the DBAL ones took about half an hour.

* Add your own code! I recommend the "controllers as services" approach taken by the included test controller, because it can make your code more readable/maintainable and your controllers more portable, but you can of course do whatever you want.

* Instead of using Silex's `return $app->json($data);` method, consider using the provided `$app['api.response']` service instead. It returns an `Inanimatt\Api\ApiResponse` object that extends the JsonResponse to include a version header, and some helpful methods: `setDocumentation($url)` and `setDeprecated(true)`. 
