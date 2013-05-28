# A minimal working Silex/OAuth 2.0-based API

Stuff it does:

* Actually bloody works. I'm not proud, but I am tired.
* Implements a Doctrine DBAL storage interface for the [LEOP OAuth server](https://github.com/php-loep/oauth2-server). (Because Silex has built-in integration for the DBAL, not because I think it's better or worse than zetacomponents).
* Provides an access token for the `client_credentials` grant type. 
* Defines a route middleware that checks for a valid access token.

Stuff it doesn't do:

* There are no Authorization Server routes (e.g. for creating & authorising clients), except for the token granting route. There's an AGPL authorisation server implementation at [fkooman/php-oauth](https://github.com/fkooman/php-oauth) if you're looking for hints on how to do this. 
* Only `client_credentials` grant type is implemented. Adding the other grant types should be straightforward, though.
* No content negotiation. 
* No JSON exceptions.
* You have to define your own scopes (just insert them into the `oauth_scopes` table).
* It doesn't define a coding structure - you should refactor `src/app.php` into whatever way you prefer to work. If you carry on with it as it is, it'll become unmaintainable very quickly. 

## Known issues

The [`php-loep/oauth2-server`](https://github.com/php-loep/oauth2-server) dependency in `composer.json` is currently pinned to `dev-develop` until a new release is made that includes a fix for the `getScopes` method on the Resource Server class. Any version newer than 2.1 will do, so if one has been released when you start using this project, change the version (e.g. `~2.2`) and do a `composer update` to install it. In the meantime, I've included a `composer.lock` that points to a working version, so you can just use that with `composer install` and everything should be fine.

