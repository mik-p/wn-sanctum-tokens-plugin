<?php

namespace mikp\sanctum\FormWidgets;

use Log;

class UpdateAuthorizationButton extends \Backend\Classes\FormWidgetBase
{

    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'updateauthorizationbutton';

    public function render()
    {
        return $this->makePartial('updateauthorizationbutton');
    }

    // ajax run console command
    public function onRunAuthorizationConsoleCommand()
    {
        Log::info("called auth command");

        $this->call('sanctum:authorization', [
            '--add' => true,
            '-y' => true
        ]);

        var_dump('hi');
    }
}
