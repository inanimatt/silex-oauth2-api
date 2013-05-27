# A minimal working Silex/OAuth 2.0-based API

Stuff it does:

* Actually bloody works. I'm not proud, but I am tired.
* Provides an access token for the `client_credentials` grant type.
* Defines a route middleware that checks for a valid access token.

Stuff it doesn't do:

* There are no Authorization Server routes (e.g. for creating & authorising clients), only the token granting route.
* Only `client_credentials` grant type is implemented.
* No content negotiation
* No JSON exceptions
* You have to define your own scopes (just insert them into the `oauth_scopes` table).
