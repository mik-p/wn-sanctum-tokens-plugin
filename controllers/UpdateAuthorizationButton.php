<?php

namespace mikp\sanctum\Controllers;

class UpdateAuthorizationButton extends \Backend\Classes\Controller
{
    // public function index()
    // {
    // }

    public function onRunAuthorizationConsoleCommand()
    {
        $this->call('mikp:authorization', [
            '--add' => true,
            '-y' => true
        ]);
    }
}
