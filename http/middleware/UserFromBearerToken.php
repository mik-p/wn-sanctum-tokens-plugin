<?php

namespace mikp\sanctum\Http\Middleware;

use mikp\sanctum\Http\Middleware\HasBearerToken;

use Auth;
use Closure;
use Response;
use Winter\User\Models\User;

class UserFromBearerToken extends HasBearerToken
{
    public function handle($request, Closure $next)
    {
        // get the authorization header
        $token = $request->bearerToken();
        if (!$token) {
            return Response::make('missing authorization header: permission denied', 403);
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
