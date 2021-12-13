<?php

namespace mikp\sanctum\Http\Controllers;

use Illuminate\Routing\Controller;

use mikp\sanctum\Models\Settings;

use Response;
use Auth;
use Illuminate\Http\Request;

class API extends Controller
{
    public $activated = false;

    public $token_limit = 3;

    public function __construct()
    {
        $this->activated = Settings::get('sanctum_enable', false);
        $this->token_limit = Settings::get('sanctum_token_limit', 3);
    }

    // routes
    // index type page
    public function index(Request $req)
    {
        $content = '<h1>Laravel Sanctum Token API</h1>';

        $base_path = $req->path();
        $content .= '<p>Base path: ' . $base_path . '/' . '</p>';

        $content .= '<p>Endpoints:</p>';

        $content .= '<ul>';
        $content .= '<li>' . $base_path . '/create' . '</li>';
        $content .= '<li>' . $base_path . '/refresh' . '</li>';
        $content .= '<li>' . $base_path . '/revoke' . '</li>';
        $content .= '</ul>';

        return Response::make(
            $content,
            200
        );
    }

    // api doc file
    public function docs()
    {
        $path = plugins_path('mikp/sanctum/api-docs.json');
        return Response::file($path, ['Content-Type' => 'application/json']);
    }

    // create token for user
    public function create(Request $request)
    {
        // active guard
        if (!$this->activated) {
            return $this->return_not_active();
        }

        // get token name from request
        $token_name = $request->input('token_name');
        if (!isset($token_name)) {
            return Response::make('bad request missing parameters, specify a token name', 400);
        }

        // has the user hit the token limit
        $user = Auth::getUser();

        $TokenModel = \Laravel\Sanctum\Sanctum::personalAccessTokenModel();

        $users_total_tokens = count($TokenModel::where('tokenable_id', $user->id)->get());

        if ($users_total_tokens >= $this->token_limit) {
            return Response::make('limit of maximum number of created tokens has been reached', 403);
        }

        // provide a new token
        $token = $user->createToken($token_name, ['api:access']);

        return ['token' => $token->plainTextToken];
    }

    public function refresh(Request $request)
    {
        // active guard
        if (!$this->activated) {
            return $this->return_not_active();
        }

        $user = Auth::getUser();

        // try get the current token
        $current_token = $user->currentAccessToken();

        // if not there get from query params
        if (!$current_token) {
            $tokenId = $request->input('tokenId', null);
            if (!$tokenId) {
                return Response::make('bad request: missing token to refresh', 400);
            }

            // find a token owned by this user by given id
            $current_token = $user->tokens()->where('id', $tokenId)->first();

            // if it doesn't exist
            if (!$current_token) {
                return Response::make('bad request: given token not found', 400);
            }
        }

        // update token created_at
        if (
            method_exists($current_token->getConnection(), 'hasModifiedRecords') &&
            method_exists($current_token->getConnection(), 'setRecordModificationState')
        ) {
            tap($current_token->getConnection()->hasModifiedRecords(), function ($hasModifiedRecords) use ($current_token) {
                $current_token->forceFill(['created_at' => now()])->save();

                $current_token->getConnection()->setRecordModificationState($hasModifiedRecords);
            });
        } else {
            $current_token->forceFill(['created_at' => now()])->save();
        }

        // if ($user->tokenCan('api:access')) {
        //     //
        //     return $user;
        // }

        return response()->json([
            'message' => 'token renewed',
            'id' => $current_token->id,
            'name' => $current_token->name,
            'abilities' => $current_token->abilities,
            'status' => 'success'
        ], 200);
    }

    public function revoke(Request $request, $tokenId)
    {
        // active guard
        if (!$this->activated) {
            return $this->return_not_active();
        }

        $user = Auth::getUser();
        // Revoke all tokens...
        // $user->tokens()->delete();

        // Revoke the token that was used to authenticate the current request...
        // $request->user()->currentAccessToken()->delete();

        // Revoke a specific token...
        $token = $user->tokens()->where('id', $tokenId)->first();

        // does it exist
        if (!$token) {
            return Response::make('bad request: given token not found', 400);
        }

        // delete it
        $id = $token->id;
        $name = $token->name;
        $abilities = $token->abilities;
        $user->tokens()->where('id', $tokenId)->delete();

        return response()->json([
            'message' => 'token revoked',
            'id' => $id,
            'name' => $name,
            'abilities' => $abilities,
            'status' => 'success'
        ], 200);
    }

    // helpers
    // api not active result
    public function return_not_active()
    {
        return Response::make('this api has not been enabled by the system administrator', 503);
    }
}
