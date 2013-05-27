# A minimal "working" Silex/OAuth 2.0-based API

Stuff it does:

* Actually bloody works. I'm not proud, but I am tired.
* Implements a Doctrine DBAL storage interface for the [LEOP OAuth server](https://github.com/php-loep/oauth2-server). (Because Silex has built-in integration for the DBAL, not because I think it's better or worse than zetacomponents).
* Provides an access token for the `client_credentials` grant type. Adding the other grant types should be straightforward from here. 
* Defines a route middleware that checks for a valid access token.

Stuff it doesn't do:

* There are no Authorization Server routes (e.g. for creating & authorising clients), only the token granting route.
* Only `client_credentials` grant type is implemented.
* No content negotiation
* No JSON exceptions
* You have to define your own scopes (just insert them into the `oauth_scopes` table).

## Why is "working" in quotes?

As of the time of writing, scopes are broken in php-leop/oauth2-server. By the time you read this, it's probably fine. The `getScopes` function accesses a 'key' column that doesn't exist - it probably means 'scope' or 'name', so if it's not working by the time you install this, and you need to check scope, modify the `isValid()` method on line 179ish of `vendor/league/oauth2-server/src/League/OAuth2/Server/Resource.php`. Change this:

```php
foreach ($sessionScopes as $scope) {
    $this->sessionScopes[] = $scope['key'];
}
```

to something like this:

```php
foreach ($sessionScopes as $scope) {
    $this->sessionScopes[] = $scope['scope']; // or name… who knows
}
```
