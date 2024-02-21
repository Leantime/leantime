# Guzzle OAuth 2.0 Subscriber

> Tested with Guzzle 4, 5, 6, 7 and PHP 5.4, 5.5, 5.6, 7.0, 7.1, 7.2, 7.3, 7.4, 8.0 and 8.1.

This is an OAuth 2.0 client for Guzzle which aims to be 100% compatible with Guzzle 4, 5, 6, 7 and all future versions within a single package.
Although I love Guzzle, its interfaces keep changing, causing massive breaking changes every 12 months or so, so I have created this package
to help reduce the dependency hell that most third-party Guzzle dependencies bring with them.  I wrote the official Guzzle OAuth 2.0 plugin
which is still on the `oauth2` branch, [over at the official Guzzle repo](https://github.com/guzzle/oauth-subscriber/tree/oauth2), but I
see that they have dropped support for Guzzle < v6 on `master`, which prompted me to split this back off to a separate package.

## Features

- Acquires access tokens via one of the supported grant types (code, client credentials,
  user credentials, refresh token). Or you can set an access token yourself.
- Supports refresh tokens (stores them and uses them to get new access tokens).
- Handles token expiration (acquires new tokens and retries failed requests).
- Allows storage and lookup of access tokens via callbacks


## Installation

This project can be installed using Composer. Run `composer require kamermans/guzzle-oauth2-subscriber` or add the following to your `composer.json`:

```javascript
    {
        "require": {
            "kamermans/guzzle-oauth2-subscriber": "~1.0"
        }
    }
```

## Usage

This plugin integrates seamlessly with Guzzle, transparently adding authentication to outgoing requests and optionally attempting re-authorization if the access token is no longer valid.

There are multiple grant types available like `PasswordCredentials`, `ClientCredentials` and `AuthorizationCode`.

### Guzzle 4 & 5 vs Guzzle 6+
With the Guzzle 6 release, most of the library was refactored or completely rewritten, and as such, the integration of this library is different.

#### Emitters (Guzzle 4 & 5)
Guzzle 4 & 5 use **Event Subscribers**, and this library includes `OAuth2Subscriber` for that purpose:

```php
$oauth = new OAuth2Subscriber($grant_type);

$client = new Client([
    'auth' => 'oauth',
]);

$client->getEmitter()->attach($oauth);
```

#### Middleware (Guzzle 6+)
Starting with Guzzle 6, **Middleware** is used to integrate OAuth, and this library includes `OAuth2Middleware` for that purpose:

```php
$oauth = new OAuth2Middleware($grant_type);

$stack = HandlerStack::create();
$stack->push($oauth);

$client = new Client([
    'auth'     => 'oauth',
    'handler'  => $stack,
]);
```

Alternatively, you can add the middleware to an existing Guzzle Client:

```php
$oauth = new OAuth2Middleware($grant_type);
$client->getConfig('handler')->push($oauth);
```


### Client Credentials Example
Client credentials are normally used in server-to-server authentication.  With this grant type, a client is requesting authorization in its own behalf, so there are only two parties involved.  At a minimum, a `client_id` and `client_secret` are required, although many services require a `scope` and other parameters.

Here's an example of the client credentials method in Guzzle 4 and Guzzle 5:

```php
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Subscriber;

// Authorization client - this is used to request OAuth access tokens
$reauth_client = new GuzzleHttp\Client([
    // URL for access_token request
    'base_url' => 'http://some_host/access_token_request_url',
]);
$reauth_config = [
    "client_id" => "your client id",
    "client_secret" => "your client secret",
    "scope" => "your scope(s)", // optional
    "state" => time(), // optional
];
$grant_type = new ClientCredentials($reauth_client, $reauth_config);
$oauth = new OAuth2Subscriber($grant_type);

// This is the normal Guzzle client that you use in your application
$client = new GuzzleHttp\Client([
    'auth' => 'oauth',
]);
$client->getEmitter()->attach($oauth);
$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
```

Here's the same example for Guzzle 6+:

```php
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use GuzzleHttp\HandlerStack;

// Authorization client - this is used to request OAuth access tokens
$reauth_client = new GuzzleHttp\Client([
    // URL for access_token request
    'base_uri' => 'http://some_host/access_token_request_url',
]);
$reauth_config = [
    "client_id" => "your client id",
    "client_secret" => "your client secret",
    "scope" => "your scope(s)", // optional
    "state" => time(), // optional
];
$grant_type = new ClientCredentials($reauth_client, $reauth_config);
$oauth = new OAuth2Middleware($grant_type);

$stack = HandlerStack::create();
$stack->push($oauth);

// This is the normal Guzzle client that you use in your application
$client = new GuzzleHttp\Client([
    'handler' => $stack,
    'auth'    => 'oauth',
]);

$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
```

### Authorization Code Example
There is a full example of using the `AuthorizationCode` grant type with a `RefreshToken` in the `examples/` directory.

### Grant Types
The following OAuth grant types are supported directly, and you can always create your own by implementing `kamermans\OAuth2\GrantType\GrantTypeInterface`:
 - `AuthorizationCode`
 - `ClientCredentials`
 - `PasswordCredentials`
 - `RefreshToken`

Each of these takes a Guzzle client as the first argument.  This client is used to obtain or refresh your OAuth access token, out of band from the other requests you are making.

### Request Signers
There are two cases where we need to *sign* an HTTP request: when adding client credentials to a request for a new access token, and when adding an access token to a request.

#### Client Credentials Signers
When requesting a new access token, we need to send the required credentials to the OAuth 2 server.  Adding information to a request is called *signing* in this library.

There are two client credentials signers included in `kamermans\OAuth2\Signer\ClientCredentials`:
 - `BasicAuth`: (default) Sends the credentials to the OAuth 2 server using HTTP Basic Auth in the `Authorization` header.
 - `PostFormData`: Sends the credentials to the OAuth 2 server using an HTTP Form Body (`Content-Type: application/x-www-form-urlencoded`).  The Client ID is stored in the field `client_id` and the Client Secret is stored in `client_secret`.  The field names can be changed by passing arguments to the constructor like this: `new PostFormData('MyClientId', 'MySecret');` (which would place the ID and secret into the fields `MyClientId` and `MySecret`).
 - `Json`: Sends the credentials to the OAuth 2 server using a JSON (`Content-Type: application/json`).  The Client ID is stored in the field `client_id` and the Client Secret is stored in `client_secret`.  The field names can be changed by passing arguments to the constructor like this: `new Json('MyClientId', 'MySecret');` (which would place the ID and secret into the fields `MyClientId` and `MySecret`).

If the OAuth 2 server you are obtaining an access token from does not support the built-in methods, you can either extend one of the built-in signers, or create your own by implementing `kamermans\OAuth2\Signer\ClientCredentials\SignerInterface`, for example:

```php
use kamermans\OAuth2\Signer\ClientCredentials\SignerInterface;

class MyCustomAuth implements SignerInterface
{
    public function sign($request, $clientId, $clientSecret)
    {
        if (Helper::guzzleIs('~', 6)) {
            $request = $request->withHeader('x-client-id', $clientId);
            $request = $request->withHeader('x-client-secret', $clientSecret);
            return $request;
        }

        $request->setHeader('x-client-id', $clientId);
        $request->setHeader('x-client-secret', $clientSecret);
        return $request;
    }
}
```

#### Access Token Signers
When making a request to a REST endpoint protected by OAuth 2, we need to *sign* the request by adding the access token to it.  This library intercepts your requests, signs them with the current access token, and sends them on their way.

The two most common ways to sign a request are included in `kamermans\OAuth2\Signer\AccessToken`:
 - `BearerAuth`: (default) Sends the access token using the HTTP `Authorization` header.
 - `BasicAuth`: Alias for `BearerAuth`. Don't use; exists for backwards compatibility only.
 - `QueryString`: Sends the access token by appending it to the query string.  The default query string field name is `access_token`, and if that field is already present in the request, it will be overwritten.  A different field name can be used by passing it to the constructor like this: `new QueryString('MyAccessToken')`, where `MyAccessToken` is the field name.

> Note: Use of the `QueryString` signer is discouraged because your access token is exposed in the URL.  Also, you should only connect to OAuth-powered services via `HTTPS` so your access token is encrypted in flight.

You can create a custom access token signer by implementing `kamermans\OAuth2\Signer\AccessToken\SignerInterface`.

### Access Token Persistence
> Note: OAuth Access tokens should be stored somewhere securely and/or encrypted.  If an attacker gains access to your access token, they could have unrestricted access to whatever resources and scopes were allowed!

By default, access tokens are not persisted anywhere.  There are some built-in mechanisms for caching / persisting tokens (in `kamermans\OAuth2\Persistence`):
  - `NullTokenPersistence` (default) Disables persistence
  - `FileTokenPersitence` Takes the path to a file in which the access token will be saved.
  - `DoctrineCacheTokenPersistence` Takes a `Doctrine\Common\Cache\Cache` object and optionally a key name (default: `guzzle-oauth2-token`) where the access token will be saved.
  - `SimpleCacheTokenPersistence` Takes a PSR-16 SimpleCache and optionally a key name (default: `guzzle-oauth2-token`) where the access token will be saved. This allows any PSR-16 compatible cache to be used.
  - `Laravel5CacheTokenPersistence` Takes an `Illuminate\Contracts\Cache\Repository` object and optionally a key name (default: `guzzle-oauth2-token`) where the access token will be saved.
  - `ClosureTokenPersistence` Allows you to define a token persistence provider by providing closures to handle the persistence functions.

If you want to use your own persistence layer, you should write your own class that implements `TokenPersistenceInterface` or use the `ClosureTokenPersistence` provider, which is described at the end of this section.

To enable token persistence, you must use the `OAuth2Middleware::setTokenPersistence()` or `OAuth2Subscriber::setTokenPersistence()` method, like this:

```php
use kamermans\OAuth2\Persistence\FileTokenPersistence;

$token_path = '/tmp/access_token.json';
$token_persistence = new FileTokenPersistence($token_path);

$grant_type = new ClientCredentials($reauth_client, $reauth_config);
$oauth = new OAuth2Middleware($grant_type);
$oauth->setTokenPersistence($token_persistence);
```
### Closure-Based Token Persistence
There are plenty of cases where you would like to use your own caching layer to store the OAuth2 data, but there is no adapter included that works with your cache provider.  The `ClosureTokenPersistence` provider makes this case easier by allowing you to define closures that handle the OAuth2 persistence data, as shown in the example below.

```php
// We'll store everything in an array, but you can use any provider you want
$cache = [];
$cache_key = "foo";

// Returns true if the item exists in cache
$exists = function() use (&$cache, $cache_key) {
    return array_key_exists($cache_key, $cache);
};

// Sets the given $value array in cache
$set = function(array $value) use (&$cache, $cache_key) {
    $cache[$cache_key] = $value;
};

// Gets the previously-stored value from cache (or null)
$get = function() use (&$cache, $cache_key, $exists) {
    return $exists()? $cache[$cache_key]: null;
};

// Deletes the previously-stored value from cache (if exists)
$delete = function() use (&$cache, $cache_key, $exists) {
    if ($exists()) {
        unset($cache[$cache_key]);
    }
};

$persistence = new ClosureTokenPersistence($set, $get, $delete, $exists);
```

> Note: The format of the token data is a PHP associative array.  You can flatten the array with `serialize()` or `json_encode()` or whatever else you want before storing it, but remember to decode it back to an array in `get()` before returning it!  Also, the above example is not very thread-safe, so if you have a high level of concurrency, you will need to find more atomic ways to handle this logic, or at least wrap things with `try/catch` and handle things gracefully.

Please see the `src/Persistence/` directory for more information on persistence.

### Manually Setting an Access Token
For a manually-obtained access token, you can use the `NullGrantType` and set the access token manually as follows:

```php
use kamermans\OAuth2\GrantType\NullGrantType;

$oauth = new OAuth2Middleware(new NullGrantType);
$oauth->setAccessToken([
	// Your access token goes here
    'access_token' => 'abcdefghijklmnop',
	// You can specify 'expires_in` as well, but it doesn't make much sense in this scenario
	// You can also specify 'scope' => 'list of scopes'
]);
```

Note that if the access token is not set using `setAccessToken()`, a `kamermans\OAuth2\Exception\ReauthorizationException` will be thrown since the `NullGrantType` has no way to get a new access token.

### Using Refresh Tokens
Refresh tokens are designed to allow a server to request a new access token on behalf of a user that is not present.  For example, if some fictional app `Angry Rodents` wants to post something to the social media site `Grillbook` on behalf of the user, `John Doe`, the `Angry Rodents` app needs an access token for `Grillbook`.  When `John Doe` first installs this app, it redirects him to the `Grillbook` site to authorize the `Angry Rodents` app to post on his behalf, and the `Angry Rodents` app receives an access token and a refresh token in the process.  Eventually the access token expires, but `Angry Rodents` cannot use the original method (redirecting the user to ask for permission) every time the token expires, so instead, it sends the refresh token to `Grillbook`, which returns a new access token (and possibly a new refresh token).

To use refresh tokens, you pass a `RefreshToken` grant type object as the second argument to `OAuth2Middleware` or `OAuth2Subscriber`.  Normally refresh tokens are only used in the interactive `AuthorizationCode` grant type (where the user is present), but it is also possible to use them with the other grant types (this is discouraged in the OAuth 2.0 spec).  For example, here we are using a refresh token with the `ClientCredentials` grant type:

```php
// This grant type is used to get a new Access Token and Refresh Token when
//  no valid Access Token or Refresh Token is available
$grant_type = new ClientCredentials($reauth_client, $reauth_config);

// This grant type is used to get a new Access Token and Refresh Token when
//  only a valid Refresh Token is available
$refresh_grant_type = new RefreshToken($reauth_client, $reauth_config);

// Tell the middleware to use the two grant types
$oauth = new OAuth2Middleware($grant_type, $refresh_grant_type);
```

> When using a refresh token to request a new access token, the server *may* send a new refresh token in the response.  If a new refresh token was sent, it will be saved, otherwise the old refresh token will be retained.
