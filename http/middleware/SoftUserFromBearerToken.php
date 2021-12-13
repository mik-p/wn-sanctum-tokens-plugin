<?php

namespace mikp\sanctum\Http\Middleware;

use mikp\sanctum\Http\Middleware\UserFromBearerToken;

use Auth;
use Closure;
use Response;
use Winter\User\Models\User;

class SoftUserFromBearerToken extends UserFromBearerToken
{
    public function handle($request, Closure $next)
    {
        // get the authorization header
        $token = $request->bearerToken();
        if (!$token) {
            // give up and pass to the next middleware
            // this is the soft version allowing other things to attempt auth
            // after bearer token because it was missing
            // next
            return $next($request);
        }

        // find a token matching the authorization
        $TokenModel = \Laravel\Sanctum\Sanctum::personalAccessTokenModel();

        $accessToken = $TokenModel::findToken($token);

        // is the token still valid
        // if (!$this->supportsTokens($accessToken->tokenable)) {
        //     return Response::make('user model does not support tokens', 500);
        // }

        if (!$this->isValidAccessToken($accessToken)) {
            return Response::make('invalid token: permission denied', 403);
        }

        // update token usage
        $accessToken = $this->updateTokenUsage($accessToken);

        // find a user with this token
        $user = User::findOrFail($accessToken->tokenable_id);

        // log the user in
        Auth::login($user);

        $accessToken->tokenable->withAccessToken($accessToken);
        $user->withAccessToken($accessToken);

        // next
        return $next($request);
    }
}
