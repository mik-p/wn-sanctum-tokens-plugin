<?php

namespace mikp\sanctum\Behaviours;

use System\Classes\ModelBehavior;

// use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens as LaravelHasApiTokens;

class HasApiTokens extends ModelBehavior
{
    // use Authenticatable;
    use Notifiable;
    use LaravelHasApiTokens;

    protected $rememberTokenName = 'persist_code';

    public function __construct($model)
    {
        parent::__construct($model);

        $model->hasMany['api_tokens'] = [\Laravel\Sanctum\Sanctum::personalAccessTokenModel(), 'key' => 'tokenable_id'];
    }

    public function morphMany($related, $name, $type = null, $id = null, $localKey = null, $relationName = null)
    {
        return $this->model->morphMany($related, $name, $type, $id, $localKey, $relationName);
    }
}
