# wn-sanctum-tokens-plugin

Add Laravel Sanctum API Tokens to Winter.Users to allow API Auth via Sanctum tokens for front end users

## depends on

This plugin depends on the Winter.User plugin for users


## usage

There are 3 endpoints that can be used to interact with tokens

- {POST} `api/v1/token/create`
- {POST} `api/v1/token/refresh`
- {POST} `api/v1/token/revoke/{tokenId}`

The **'create'** endpoint does not act as a log in, it is guarded with the \Winter\User\Classes\AuthMiddleware which means the user must already be logged in.

The other endpoints are guarded via token so you need to `create` a token and provide it as Authorization before you can `revoke` or `refresh` any.

### middleware

Add the provided middleware to routes for example:

```php
// in your plugin's routes.php file

Route::group([
    'prefix' => 'api',
    'middleware' => [
        'api',
        'mikp\sanctum\Http\Middleware\UserFromBearerToken'
    ]
], function () {

    // do a thing
    Route::post('/thing', 'Author\Plugin\Http\Controllers\API@thing');
});
```
