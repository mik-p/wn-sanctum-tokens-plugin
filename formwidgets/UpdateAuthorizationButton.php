<?php

namespace mikp\sanctum\FormWidgets;

class UpdateAuthorizationButton extends \Backend\Classes\FormWidgetBase
{

    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'updateauthorizationbutton';

    public function render()
    {
        return $this->makePartial('updateauthorization');
    }

    public function onRunAuthorizationConsoleCommand()
    {
        $this->call('mikp:authorization', [
            '--add' => true,
            '-y' => true
        ]);
    }
}
