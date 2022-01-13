# wn-sanctum-tokens-plugin

[![Buy me a tree](https://img.shields.io/badge/Buy%20me%20a%20tree-%F0%9F%8C%B3-green)](https://ecologi.com/mik-p-online?gift-trees)
[![Plant a Tree for Production](https://img.shields.io/badge/dynamic/json?color=brightgreen&label=Plant%20a%20Tree%20for%20Production&query=%24.total&url=https%3A%2F%2Fpublic.offset.earth%2Fusers%2Ftreeware%2Ftrees)](https://plant.treeware.earth/mik-p/wn-sanctum-tokens-plugin)

Add Laravel Sanctum API Tokens to Winter.Users to allow API Auth via Sanctum tokens for front end users. It is useful if you need API Bearer Token Auth and don't want to go with OAuth2.

### depends on

This plugin depends on [Laravel Sanctum](https://github.com/laravel/sanctum) for tokens

This plugin depends on the [Winter.User plugin](https://github.com/wintercms/wn-user-plugin) for users

#### .htaccess headers

The default wintercms settings do not allow the needed token header. If you are using Apache there is a console command and button in the backend settings to add the necessary modifications to the .htaccess file

```bash
# adds the following to the root .htaccess file

# ##
# ## Authorization header
# ##
# RewriteCond %{HTTP:Authorization} ^(.*)
# RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]

php artisan sanctum:authorization --add
```

## usage

There are 3 endpoints that can be used to interact with tokens

- {POST} `api/v1/sanctum/token/create`
- {POST} `api/v1/sanctum/token/refresh`
- {POST} `api/v1/sanctum/token/revoke/{tokenId}`

The **'create'** endpoint does not act as a log in, it is guarded with the \Winter\User\Classes\AuthMiddleware which means the user must already be logged in.

The other endpoints are guarded via token so you need to `create` a token and provide it as Authorization before you can `revoke` or `refresh` any.

### middleware

There are three middleware classes:
1. HasBearerToken => allow if there is a valid token present
1. UserFromBearerToken => log the user in using a valid token
1. SoftUserFromBearerToken => try to log the user in with a valid token or give up and pass the request on to next middleware

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

### components

There is an api-token creation component that can be used to allow users to create and revoke their own tokens.

```php
'mikp\sanctum\Components\CreateToken' => 'createtoken'
```

## Licence

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/mik-p/wn-sanctum-tokens-plugin) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.
