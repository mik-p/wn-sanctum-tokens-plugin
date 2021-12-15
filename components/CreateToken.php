<?php

namespace mikp\sanctum\Components;

use Auth;
use Cms\Classes\ComponentBase;
use mikp\sanctum\Models\Settings;

class CreateToken extends ComponentBase
{
    public $activated = false;

    public $api_basepath = '/api/v1/sanctum/token';

    protected $expiration = 1;

    public function componentDetails()
    {
        return [
            'name'        => 'Sanctum Token Button',
            'description' => 'Create an Sanctum API Auth token for the current user'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
        $this->activated = Settings::get('sanctum_enable', false);
        $this->expiration = Settings::get('sanctum_token_expiration', $this->expiration);
    }

    public function getTokens()
    {
        // get current user's tokens
        $user = Auth::getUser();
        $tokens = $user->tokens()->get();

        $token_array = [];

        foreach ($tokens as $token) {

            $expired = !$token->created_at->gt(now()->subMinutes($this->expiration));

            $token_array[] = [
                'tokenId' => $token->id,
                'tokenName' => $token->name,
                'createdAt' => $token->created_at,
                'lastUsed' => $token->last_used_at,
                'expiresIn' =>  $expired ? 'Expired' : now()->diffInMinutes($token->created_at->addMinutes($this->expiration))
            ];
        }

        return $token_array;
    }

    public function onTokenListChange()
    {
        return [];
    }
}
