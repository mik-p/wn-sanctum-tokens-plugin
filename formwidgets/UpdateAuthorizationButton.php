<?php

namespace mikp\sanctum\FormWidgets;

use Log;
use Artisan;

class UpdateAuthorizationButton extends \Backend\Classes\FormWidgetBase
{

    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'updateauthorizationbutton';

    public function render()
    {
        return $this->makePartial('updateauthorizationbutton', ['success' => 'command not run..']);
    }

    // ajax run console command
    public function onRunAuthorizationConsoleCommand()
    {
        $ret = Artisan::call(
            'sanctum:authorization',
            [
                '--add' => true,
                '-y' => true
            ]
        );

        Log::info("called auth header update command");

        // var_dump('hi');
        if ($ret) {
            echo ('command failed');
        }

        return [
            'partial' => $this->makePartial('updateauthorizationbutton', ['success' => 'command was run..'])
        ];
        // return $ret;
    }
}
