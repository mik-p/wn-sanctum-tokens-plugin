<?php

namespace mikp\sanctum\Http\Middleware;

use Closure;
use Response;
use Config;
use mikp\sanctum\Models\Settings;

class HasBearerToken
{
    protected $expiration = 1;

    public function __construct()
    {
        $this->expiration = Config::get('mikp.sanctum::expiration', 1);
        $this->expiration = Settings::get('sanctum_token_expiration', $this->expiration);
    }

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

        // next
        return $next($request);
    }

    protected function updateTokenUsage($accessToken)
    {
        // update token usage
        if (
            method_exists($accessToken->getConnection(), 'hasModifiedRecords') &&
            method_exists($accessToken->getConnection(), 'setRecordModificationState')
        ) {
            tap($accessToken->getConnection()->hasModifiedRecords(), function ($hasModifiedRecords) use ($accessToken) {
                $accessToken->forceFill(['last_used_at' => now()])->save();

                $accessToken->getConnection()->setRecordModificationState($hasModifiedRecords);
            });
        } else {
            $accessToken->forceFill(['last_used_at' => now()])->save();
        }

        return $accessToken;
    }

    protected function isValidAccessToken($accessToken): bool
    {
        if (!$accessToken) {
            return false;
        }

        return $accessToken->created_at->gt(now()->subMinutes($this->expiration));
    }
}
