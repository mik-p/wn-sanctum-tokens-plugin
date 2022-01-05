<?php

namespace mikp\sanctum;

use System\Classes\PluginBase;

use App;
use Config;
use Auth;
use Winter\User\Models\User as UserModel;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\AliasLoader;
use System\Classes\SettingsManager;

class Plugin extends PluginBase
{
    public $elevated = true;

    public $require = ['Winter.User'];

    public $middlewareAliases = [
        'auth' => \mikp\sanctum\Classes\Authenticate::class,
        'sanctum.abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        'sanctum.ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class
    ];

    public function registerComponents()
    {
        return [
            'mikp\sanctum\Components\CreateToken' => 'createtoken',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Sanctum API Tokens',
                'description' => 'Manage Sanctum API Token settings.',
                'category'    => SettingsManager::CATEGORY_USERS,
                'icon'        => 'wn-icon-key',
                'class'       => 'mikp\sanctum\Models\Settings',
                'order'       => 600,
                'keywords'    => 'sanctum api token auth',
                'permissions' => ['mikp.sanctum.settings']
            ]
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'mikp\sanctum\FormWidgets\UpdateAuthorizationButton' => 'updateauthorizationbutton',
        ];
    }

    public function boot()
    {
        // set config
        Config::set('sanctum', Config::get('mikp.sanctum::config'));
        Config::set('auth', Config::get('mikp.sanctum::auth'));

        // extend user with api tokens
        UserModel::extend(function ($model) {
            $model->implement[] = \mikp\sanctum\Behaviours\HasApiTokens::class;
        });

        // register laravel auth
        App::singleton('auth', function ($app) {
            // return new \Illuminate\Auth\AuthManager($app);
            return \Winter\User\Classes\AuthManager::instance();
        });

        App::register('\Illuminate\Auth\AuthServiceProvider');

        // $this->app['router']->aliasMiddleware('auth', \Illuminate\Auth\Middleware\Authenticate::class);

        // var_dump(App::make('user.auth'));
        // var_dump($this->app['router']);
        // var_dump($auth = App::make('auth'));

        // sanctum providers, aliases, middleware
        App::register('\Laravel\Sanctum\SanctumServiceProvider');

        // aliases
        $facade = AliasLoader::getInstance();
        $facade->alias('Sanctum', '\Laravel\Sanctum\Sanctum');

        // middleware
        // Boot middleware aliases
        $this->aliasMiddleware();
        // $this->app['router']->middleware('sanctum.abilities', \Laravel\Sanctum\Http\Middleware\CheckAbilities::class);
        // $this->app['router']->middleware('sanctum.ability', \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class);

        Sanctum::ignoreMigrations();
    }

    public function register()
    {
        // register console command
        $this->registerConsoleCommand('sanctum.authorization', 'mikp\sanctum\Console\EnableAuthorizationHeader');
    }

    /**
     * Registers provided middleware aliases with the router
     */
    protected function aliasMiddleware()
    {
        $router = $this->app['router'];

        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';

        foreach ($this->middlewareAliases as $alias => $middleware) {
            $router->$method($alias, $middleware);
        }
    }
}
