# A minimal working Silex/OAuth 2.0-based API

Stuff it does:

* Actually bloody works. I'm not proud, but I am tired.

* Implements a Doctrine DBAL storage interface for the [LEOP OAuth server](https://github.com/php-loep/oauth2-server). (Because Silex has built-in integration for the DBAL, not because I think it's better or worse than zetacomponents).

* Provides an access token for the `client_credentials` grant type. 

* Defines a route middleware service that checks for a valid access token.

* Basic JSON exception handling

* Example of coding framework-independent controllers as services, registered with a Silex provider.

* Registers a listener that converts array responses to JSON responses so that your framework-independent controllers aren't dependent on the Symfony JsonResponse class (because that'd defeat the point a bit).

Stuff it doesn't do:

* There are no Authorization Server routes (e.g. for creating & authorising clients), except for the token granting route. There's an authorisation server implementation at [fkooman/php-oauth](https://github.com/fkooman/php-oauth) if you're looking for hints on how to do this. 

* Only the `client_credentials` grant type is implemented. Adding the other grant types should be straightforward, though, and `client_credentials` might be just fine depending on what your API does.

* No proper content negotiation; just dies if `application/json` isn't accepted.

* You must define your own scopes (just insert them into the `oauth_scopes` table), or resign yourself to not using them.

## Known issues

None yet…!