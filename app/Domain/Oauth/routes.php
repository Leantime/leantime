<?php

use Illuminate\Support\Facades\Route;
use Leantime\Core\Auth\OAuth\OAuthServiceProvider;
use Leantime\Domain\Oauth\Controllers\Authorize;
use Leantime\Domain\Oauth\Controllers\Metadata;
use Leantime\Domain\Oauth\Controllers\Register;
use Leantime\Domain\Oauth\Controllers\Token;

/*
|--------------------------------------------------------------------------
| OAuth sign-in for MCP clients (Claude, ChatGPT, …)
|--------------------------------------------------------------------------
|
| Discovery, dynamic client registration and the token endpoint are public (AuthCheck lets them
| through). /oauth/authorize is an ordinary web page: AuthCheck signs the user in first.
| LEAN_OAUTH_ENABLED=false removes every route here.
|
*/

if (! OAuthServiceProvider::enabled()) {
    return;
}

Route::get('/.well-known/oauth-protected-resource/{path?}', [Metadata::class, 'protectedResource'])
    ->where('path', '.*')
    ->name('oauth.metadata.protectedResource');

Route::get('/.well-known/oauth-authorization-server/{path?}', [Metadata::class, 'authorizationServer'])
    ->where('path', '.*')
    ->name('oauth.metadata.authorizationServer');

Route::post('/oauth/register', [Register::class, 'post'])->name('oauth.register');
Route::post('/oauth/token', [Token::class, 'issue'])->name('oauth.token');

Route::get('/oauth/authorize', [Authorize::class, 'show'])->name('oauth.authorize');
Route::post('/oauth/authorize', [Authorize::class, 'approve'])->name('oauth.authorize.approve');
Route::post('/oauth/authorize/deny', [Authorize::class, 'deny'])->name('oauth.authorize.deny');
