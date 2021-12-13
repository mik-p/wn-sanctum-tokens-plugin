<?php //namespace mikp\sanctum\Routes;

$version = 'v1';
$api_name = '/sanctum';
$base_uri = '/api/' . $version . $api_name;

// api doc json file
Route::get($base_uri . '/sanctum/api-docs.json', 'mikp\sanctum\Http\Controllers\API@docs');

// token API
Route::group([
    'prefix' => $base_uri . '/token',
    'middleware' => [
        'api',
        'web',
        'Winter\User\Classes\AuthMiddleware',
    ]
], function () {

    // index
    Route::get('/', 'mikp\sanctum\Http\Controllers\API@index');

    // create a token
    Route::post('/create', 'mikp\sanctum\Http\Controllers\API@create');
    // Route::get('/create', 'mikp\sanctum\Http\Controllers\API@create');
});

Route::group([
    'prefix' => $base_uri . '/token',
    'middleware' => [
        'api',
        'web',
        // 'mikp\sanctum\Http\Middleware\UserFromBearerToken',
        'mikp\sanctum\Http\Middleware\SoftUserFromBearerToken',
        'Winter\User\Classes\AuthMiddleware',
        // 'auth',
        // 'auth:api',
        // 'auth:sanctum',
    ]
], function () {

    // refresh token
    Route::post('/refresh', 'mikp\sanctum\Http\Controllers\API@refresh');

    // revoke token
    Route::post('/revoke/{tokenId}', 'mikp\sanctum\Http\Controllers\API@revoke');
});
